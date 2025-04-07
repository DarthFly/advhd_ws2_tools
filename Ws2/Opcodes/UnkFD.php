<?php
namespace Ws2\Opcodes;

/**
 * FD
 */
class UnkFD extends AbstractUndefined
{
    public const OPCODE = 'FD';
    public const FUNC = 'UnkFD';

    public function getSize(): int
    {
        return 0;
    }
}