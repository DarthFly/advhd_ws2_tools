<?php
namespace Ws2\Opcodes;

class Unk17 extends AbstractUndefined
{
    public const OPCODE = '17';
    public const FUNC = 'Unk17';

    public function getSize(): int
    {
        return 1;
    }
}