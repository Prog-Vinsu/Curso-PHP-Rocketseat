<?php

namespace App;

enum ProjectStatus: string
{
    case Open = 'open';

    case Closed = 'closed';

    public function label():string {

        return match ($this) {
            self::Open => 'Aceitando Propostas',
            
            self::Closed => 'Encerrado'
        };
    }
}
