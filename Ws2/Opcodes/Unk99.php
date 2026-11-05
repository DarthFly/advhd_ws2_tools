<?php
namespace Ws2\Opcodes;

/**
 * 989 [string=effect] 00
 * [int16][int16][int16]
 */
class Unk99 extends AbstractOpcode
{
    public const OPCODE = '99';
    public const FUNC = 'Unk99';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$effect, $size] = $this->reader->readString($dataSource);
        $word1 = $this->reader->readWord($dataSource);
        $word2 = $this->reader->readWord($dataSource);
        $word3 = $this->reader->readWord($dataSource);
        $this->compiledSize = 1 + $size + 2*3;

        $this->content = static::FUNC . " ({$effect}, {$word1}, {$word2}, {$word3})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]);
        unset($params[0]);
        $code .= pack('v3',
            ...$params
        );
        $this->content = $code;
        return $this;
    }
}
