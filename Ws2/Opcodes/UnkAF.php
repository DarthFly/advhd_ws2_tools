<?php
namespace Ws2\Opcodes;

/**
 *
 */
class UnkAF extends AbstractOpcode
{
    public const OPCODE = 'AF';
    public const FUNC = 'UnkAF';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        $dword = $this->reader->readDWord($dataSource);
        $floats = $this->reader->readFloats($dataSource, 4);
        $this->compiledSize = 1 + 4 + 4 * 4;

        $this->content = static::FUNC . " ({$dword}, ".implode(', ', $floats).")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);

        $code = $this->reader->convertHexToChar(static::OPCODE) .
            pack('Vf4',
                (int)$params[0],
                (float)$params[1],
                (float)$params[2],
                (float)$params[3],
                (float)$params[4],
            );
        $this->content = $code;
        return $this;
    }
}
