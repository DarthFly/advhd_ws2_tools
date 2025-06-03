<?php
namespace Ws2\Opcodes;

/**
 * Dynamic LENGTH
 */
class UnkScreen extends AbstractOpcode
{
    public const OPCODE = 'F0';
    public const FUNC = 'UnkScreen';

    public function decompile(\Helper\FastBuffer &$dataSource): self
    {
        $config = $this->reader->readData($dataSource, 1);
        $this->compiledSize = 2;

        $this->content = static::FUNC . " (".implode(', ', $config).")";
        return $this;
    }

    public function preCompile(?string $params = null): self
    {
        $params = $this->reader->unpackParams($params);
        $params[0] = (int)$params[0];
        $code = $this->reader->convertHexToChar(static::OPCODE) . pack('c', $params[0]);
        if (count($params) > 2) {
            $code .= pack('fVV', (float)$params[1], (int)$params[2], (int)$params[3]);
        }
        $this->content = $code;
        $this->compiledSize = 2;
        return $this;
    }
}
