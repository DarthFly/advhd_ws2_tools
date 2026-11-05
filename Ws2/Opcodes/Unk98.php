<?php
namespace Ws2\Opcodes;

/**
 * 98 [string=effect] 00
 * [int16][int16][float x 6][int16]
 */
class Unk98 extends AbstractOpcode
{
    public const OPCODE = '98';
    public const FUNC = 'Unk98';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$effect, $size] = $this->reader->readString($dataSource);
        $word1 = $this->reader->readWord($dataSource);
        $word2 = $this->reader->readWord($dataSource);
        $floats = $this->reader->readFloats($dataSource,6);
        $word3 = $this->reader->readWord($dataSource);
        $this->compiledSize = 1 + $size + 2*2 + 4*6 + 2;

        $this->content = static::FUNC . " ({$effect}, {$word1}, {$word2}, " . implode(', ', $floats) . ", {$word3})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]);
        unset($params[0]);
        $code .= pack('v2f6v',
            ...$params
        );
        $this->content = $code;
        return $this;
    }
}
