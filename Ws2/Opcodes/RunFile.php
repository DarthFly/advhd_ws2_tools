<?php
namespace Ws2\Opcodes;

/**
 * 04 [string][NULL]
 */
class RunFile extends AbstractNullByteText
{
    public const OPCODE = '04';
    public const FUNC = 'RunFile';
}