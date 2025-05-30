<?php
namespace Ws2\Opcodes;

/**
 * 97 [byte * 3][float * 4]
 */
class Unk97 extends AbstractOpcode
{
    public const OPCODE = '97';
    public const FUNC = 'Unk97';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        $config = $this->reader->readData($dataSource, 3);
        $floats = $this->reader->readFloats($dataSource,4);
        $this->compiledSize = 1 + 3 + 4*4;

        $this->content = static::FUNC . " (" . implode(', ', $config) . ", " . implode(', ', $floats) . ")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            pack('c3f4',
                ...$params
            );
        $this->content = $code;
        return $this;
    }
}
