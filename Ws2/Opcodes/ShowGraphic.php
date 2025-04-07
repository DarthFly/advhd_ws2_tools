<?php
namespace Ws2\Opcodes;

class ShowGraphic extends AbstractNullByteText
{
    public const OPCODE = '66';
    public const FUNC = 'ShowGraphic';

    protected ?int $validateKey = 0;
}