<?php

namespace App\Livewire\Proposals;

use App\Actions\ArrangePositions;
use App\Models\Project;
use App\Models\Proposal;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Create extends Component
{
    public Project $project;
    
    public bool $modal  = false;

    public bool $agree = false;


    #[Rule(['required', 'email'])]
    public string $email = '';

    #[Rule(['required', 'numeric', 'min:1'])]
    public int $hours = 0;

    public function save() {
        $this->validate();

        if(!$this->agree) {
            $this->addError('agree', "Você precisa concordar com os termos de uso!");
            return;
        }

        DB::transaction(function() {
            $proposal = $this->project->proposals()
                ->updateOrCreate(
                    ['email' => $this->email]
                    ,['hours' => $this->hours]
                );

            $this->arrangePositions($proposal);

        });

        $this->dispatch('proposal::created');

        $this->modal = false;
    }

    public function arrangePositions(Proposal $proposal) {
        $query = DB::select("
            SELECT p.*, (@rownum := @rownum + 1) AS newPosition
            FROM proposals p, (SELECT @rownum := 0) r
            WHERE p.project_id = :project
            ORDER BY p.hours ASC
        ", ['project' => $proposal->project_id]);

        // Encontra a posição da proposta atual
        $position = collect($query)->firstWhere('id', $proposal->id);

        if ($position) {
            // Encontra a outra proposta com base na nova posição
            $otherProposal = collect($query)->firstWhere('newPosition', $position->newPosition);

            if ($otherProposal) {
                // Atualiza a proposta original para 'up'
                Proposal::query()->where('id', $proposal->id)->update(['position_status' => 'up']);
                
                // Atualiza a outra proposta para 'down'
                Proposal::query()->where('id', $otherProposal->id)->update(['position_status' => 'down']);
            }
        }

        // Reordena as posições novamente se necessário
        ArrangePositions::run($proposal->project_id);


    }

    public function render()
    {
        return view('livewire.proposals.create');
    }
}
