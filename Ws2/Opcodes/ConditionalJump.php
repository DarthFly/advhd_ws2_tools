<?php
namespace Ws2\Opcodes;

/**
 * E6 [int32][int32]
 */
class ConditionalJump extends AbstractOpcodeWithPointer
{
    public const OPCODE = 'E6';
    public const FUNC = 'ConditionalJump';

    public function decompile(array &$dataSource): self
    {
        $pointerId = $this->reader->readDWord($dataSource);
        $pointerId2 = $this->reader->readDWord($dataSource);
        $this->compiledSize = 1 + 4 + 4;
        $this->pointers[] = $pointerId;
        $this->pointers[] = $pointerId2;
        $this->content = static::FUNC . " (@pointer_{$pointerId}, @pointer_{$pointerId2})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        [$pointer, $pointer2] = $this->reader->unpackParams($params);
        $code = $this->reader->convertHexToChar(static::OPCODE);
        if ($pointer > 0 || $pointer2 > 0) {
            $this->pointers[] = $pointer;
            $this->pointers[] = $pointer2;
        } else {
            $code .= pack('V2', $pointer, $pointer2);
        }
        $this->compiledSize = 9;
        $this->content = $code;
        return $this;
    }
}