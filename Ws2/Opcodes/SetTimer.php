<?php
namespace Ws2\Opcodes;

/**
 * 11 [string=name][NULL][>v1.4 byte][float]
 */
class SetTimer extends AbstractOpcode
{
    public const OPCODE = '11';
    public const FUNC = 'SetTimer';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$name, $len] = $this->reader->readString($dataSource);
        if ($this->version > 1.4) {
            $configBytes = $this->reader->readData($dataSource, 1);
            $this->compiledSize += 1;
        } else {
            $configBytes = [0];
        }
        $seconds = $this->reader->readFloat($dataSource);
        $this->compiledSize += 1 + $len + 4;

        $this->content = static::FUNC . " ({$name}, ".implode(', ', $configBytes).", {$seconds})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]);

        if ($this->version > 1.4) {
            $code .= pack('cf', (int)$params[1], (float)$params[2]);
        } else {
            $code .= pack('f', (float)$params[2]);
        }
        $this->content = $code;
        return $this;
    }
}
