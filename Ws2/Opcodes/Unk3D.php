<?php
namespace Ws2\Opcodes;

/**
 * 3D [byte * 2]
 */
class Unk3D extends AbstractUndefined
{
    public const OPCODE = '3D';
    public const FUNC = 'Unk3D';

    public function getSize(): int
    {
        return 2;
    }
}