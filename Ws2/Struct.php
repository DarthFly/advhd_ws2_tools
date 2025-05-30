<?php
namespace Ws2;

use Exception;
use Helper\TextExtractor;
use Ws2\Opcodes\AbstractOpcode;

class Struct
{
    protected int $totalSize = 0;

    protected array $labels = [];

    protected \Helper\FastBuffer $data;

    public function __construct(
        protected Reader $reader,
        protected OpcodesList $opcodesList,
        protected array $data_array,
        private TextExtractor $textExtractor,
        protected int $offset = 0,
    ) {
        $this->data = new \Helper\FastBuffer($this->data_array);
    }

    public function generateScript(float $version, int $updateMode = 0, int $offset = 0): array
    {
        $this->totalSize = $this->data->count();
        $script = [];
        $isFileStart = true;
        // Only for title.ws2, as it has empty zero offset at the start which could not be read in normal ways
        while ($offset > 0) {
            $opcode = new \Ws2\ZeroOpcode($this->reader, $version, $updateMode);
            $opcode->decompile($this->data);
            $script[] = $opcode;
            $offset --;
        }
        while($this->totalSize > 0) {
            $command = $this->data->shift();
            $hex = $this->reader->getHex($command);
            if ($isFileStart && $hex === '00') {
                $isFileStart = false;
            }
            try {
                $class = $this->opcodesList->getByOpcode($hex);
                $class = 'Ws2\\Opcodes\\' . $class;
                /** @var AbstractOpcode $opcode */
                $opcode = new $class($this->reader, $version, $updateMode, $this->textExtractor);
                $opcode->decompile($this->data);
                $this->registerLabels($opcode);
                $script[] = $opcode;
                $isFileStart = false;
            } catch (Exception $e) {
                $this->processOpcodeException($command, $script, $e->getMessage());
            }
            $this->totalSize -= $opcode->getCompiledSize();
        }
        $script = $this->processLabels($script);
        foreach ($script as $key => $opcode) {
            $script[$key] = is_object($opcode) ? $opcode->getContent() : $opcode;
        }
        return $script;
    }

    private function processOpcodeException(int $command, array $script, string $message)
    {
        $hex = $this->reader->getHex($command);
        $debugLine = $this->generateOutputLine(32);
        foreach ($script as $key => $opcode) {
            $script[$key] = is_object($opcode) ? $opcode->getContent() : $opcode;
        }

        file_put_contents('debug.log', implode("\n", $script));
        //echo "Script:\n" . implode("\n", $script) . "\n\n";
        throw new Exception($message . ". Debug: [".$hex."]\n" . $debugLine);
    }

    private function generateOutputLine(int $displayNumbers, int $perLine = 8): string
    {
        if ($this->data->isEmpty()) {
            return 'Full script processed';
        }
        $lines = [];
        $result = [];
        for ($i=0; $i<$displayNumbers; $i++) {
            if ($i > 0 && $i % $perLine === 0) {
                $result[] = implode(' ', $lines);
                $lines = [];
            }
            $command = $this->data->shift();
            $hex = $this->reader->getHex($command);
            $lines[] = $hex;
            if ($this->data->isEmpty()) {
                break;
            }
        }
        if (!empty($lines)) {
            $result[] = implode(' ', $lines);
        }

        return implode("\n", $result);
    }

    private function registerLabels(AbstractOpcode $opcode)
    {
        $pointers = $opcode->getPointers();
        if (empty($pointers)) {
            return;
        }
        // No need to register it second time again
        foreach ($pointers as $pointerId) {
            if (isset($this->labels[$pointerId])) {
                $opcode->setPointerLabel($pointerId, $this->labels[$pointerId]);
                return;
            }
            $this->labels[$pointerId] = '@LABEL_' . $pointerId;
            $opcode->setPointerLabel($pointerId, $this->labels[$pointerId]);
        }
    }

    /**
     * @param AbstractOpcode[] $script
     * @return AbstractOpcode[]
     * @throws Exception
     */
    private function processLabels(array $script): array
    {
        if (empty($this->labels)) {
            return $script;
        }
        $result = [];
        $position = 0;
        if (isset($this->labels[$position])) {
            $result[] = $this->labels[$position];
            unset($this->labels[$position]);
        }
        while($opcode = array_shift($script)) {
            $position += $opcode->getCompiledSize();
            $result[] = $opcode;
            if (isset($this->labels[$position])) {
                $result[] = $this->labels[$position];
                unset($this->labels[$position]);
            }
        }
        if (!empty($this->labels)) {
            print_r($this->labels);
            $this->processOpcodeException(6, $result, 'Not all labels are cleared');
        }
        return $result;
    }
}
