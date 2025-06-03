<?php
namespace Ws2\Opcodes;

use Exception;

/**
 * 36 [string=channel][NULL]
 * [float * 4][float * 3][byte * 2]
 */
class PrepareBackgroundArea extends AbstractOpcode
{
    public const OPCODE = '36';
    public const FUNC = 'PrepareBackgroundArea';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$channel, $channelLen] = $this->reader->readString($dataSource);
        $floatWidth = $this->reader->readFloat($dataSource);
        $floatHeight = $this->reader->readFloat($dataSource);
        $floatPos1 = $this->reader->readFloat($dataSource);
        $floatPos2 = $this->reader->readFloat($dataSource);
        $floatZero1 = $this->reader->readFloat($dataSource);
        $floatZero2 = $this->reader->readFloat($dataSource);
        $floatZero3 = $this->reader->readFloat($dataSource);
        $options = $this->reader->readData($dataSource, 2);
        $this->compiledSize = 1 + $channelLen + 4 * 7 + 2;
        if ($floatZero1 != 0 || $floatZero2 != 0 || $floatZero3 != 0) {
            exit("PrepareBackgroundArea[36] - Floats changed - {$floatZero1}/{$floatZero2}/{$floatZero3}");
        }

        $this->content = static::FUNC . " ({$channel}, {$floatWidth}, {$floatHeight}, {$floatPos1}, {$floatPos2}, " .
            "{$floatZero1}, {$floatZero2}, {$floatZero3}, " . implode(', ', $options) . ")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $this->content = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            pack('f4', (float)$params[1], (float)$params[2], (float)$params[3], (float)$params[4]) .
            pack('f3c2', (float)$params[5], (float)$params[6], (float)$params[7], (int)$params[8], (int)$params[9]);
        return $this;
    }
}
