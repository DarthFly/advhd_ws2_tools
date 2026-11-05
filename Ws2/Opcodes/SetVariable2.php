<?php
namespace Ws2\Opcodes;

/**
 * 6B [string=variable][NULL][string=value][NULL]
 * Possible Copy or alias, who knows
 */
class SetVariable2 extends AbstractOpcode
{
    public const OPCODE = '6B';
    public const FUNC = 'SetVariable2';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        [$variable, $len] = $this->reader->readString($dataSource);
        [$value, $valueLen] = $this->reader->readString($dataSource);
        $this->compiledSize = 1 + $len + $valueLen;

        $this->content = static::FUNC . " ({$variable}, {$value})";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $this->content = $this->reader->convertHexToChar(static::OPCODE) .
            $this->reader->packString($params[0]) .
            $this->reader->packString($params[1]);
        return $this;
    }
}
