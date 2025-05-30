<?php
namespace Ws2\Opcodes;

/**
 * 5B [string=keyname][NULL][int16][byte]
 */
class InitKeyName extends AbstractOpcode
{
    public const OPCODE = '5B';
    public const FUNC = 'InitKeyName';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$keyName, $len] = $this->reader->readString($dataSource);
        $id = $this->reader->readWord($dataSource);
        $config = $this->reader->readData($dataSource, 1);
        $this->compiledSize = 1 + $len + 2 + 1;

        $this->content = static::FUNC . " ({$keyName}, {$id}, ".implode(', ', $config).")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            pack('vc', (int)$params[1], (int)$params[2]);
        $this->content = $code;
        return $this;
    }
}
