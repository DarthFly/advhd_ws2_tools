<?php
namespace Ws2\Opcodes;

/**
 * 73 [string=name][NULL][string=keyname][NULL][byte * 2]
 */
class SetPnaFile extends AbstractOpcode
{
    public const OPCODE = '73';
    public const FUNC = 'SetPnaFile';

    public function decompile(array &$dataSource): self
    {
        [$layer, $layerLen] = $this->reader->readString($dataSource);
        [$filename, $nameLen] = $this->reader->readString($dataSource); // ?
        $config = $this->reader->readData($dataSource, 2);
        $this->compiledSize = 1 + $layerLen + $nameLen + 2;

        $this->content = static::FUNC . " ({$layer}, {$filename}, ".implode(', ', $config).")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            $this->reader->packString($params[1]) .
            pack('c2', (int)$params[2], (int)$params[3]);
        $this->content = $code;
        return $this;
    }
}