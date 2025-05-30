<?php
namespace Ws2\Opcodes;

/**
 * 75 [string=name][NULL][string=keyname][NULL]
 */
class Unk75 extends AbstractOpcode
{
    public const OPCODE = '75';
    public const FUNC = 'Unk75'; // Set Variable From Another Variable?

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$name, $nameLen] = $this->reader->readString($dataSource);
        [$keyname, $keynameLen] = $this->reader->readString($dataSource); // ?
        $this->compiledSize = 1 + $nameLen + $keynameLen;

        $this->content = static::FUNC . " ({$name}, {$keyname})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            $this->reader->packString($params[1]);
        $this->content = $code;
        return $this;
    }
}
