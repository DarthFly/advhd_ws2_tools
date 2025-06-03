<?php
namespace Ws2\Opcodes;

use Exception;
use Ws2\FilesValidator;

/**
 * Class ShowChoice
 */
class ShowChoice extends AbstractOpcodeWithPointer
{
    public const OPCODE = '0F';
    public const FUNC = 'ShowChoice';

    public function decompile(array &$dataSource): self
    {
        [$choiceAmount] = $this->reader->readData($dataSource, 1); // Assumed ???
        $choices = '';
        $this->compiledSize = 1 + 1;
        for ($i=0; $i < $choiceAmount; $i++) {
            $choiceId = $this->reader->readWord($dataSource);
            [$text, $textLen] = $this->reader->readString($dataSource);
            [$op1, $op2, $op3, $opJump] = $this->reader->readData($dataSource, 4);
            $this->compiledSize += 2 + $textLen + 4;
            if ($opJump === 6) {
                $pointer = $this->reader->readDWord($dataSource);
                $this->compiledSize += 4;
                if ($pointer !== 0) {
                    $this->pointers[] = $pointer;
                    $pointer = '@pointer_' . $pointer;
                }
            } elseif ($opJump === 7) {
                [$pointer, $textLen] = $this->reader->readString($dataSource);
                $this->compiledSize += $textLen;
            } else {
                throw new \Exception('Unsupported sub-opcode in condition: ' . $opJump);
            }
            $choices .= "{$choiceId}, {$op1}, {$op2}, {$op3}, {$opJump}, {$pointer}\n$text\n";
        }
        $this->content = static::FUNC . " ({$choiceAmount}\n".$choices.");";
        return $this;
    }

    public function preCompile(?string $params = null, ?array &$scriptLines = [], int &$messageIdOverride = 0): self
    {
        [$choiceAmount] = $this->reader->unpackParams($params . ')');
        $this->compiledSize = 1 + 1;
        $code = $this->reader->convertHexToChar(static::OPCODE) .
            pack('c', (int)$choiceAmount);

        for ($i=0; $i < $choiceAmount; $i++) {
            $messageParams = array_shift($scriptLines);
            [$choiceId, $op1, $op2, $op3, $opJump, $pointer] = explode(', ', $messageParams);
            $text = array_shift($scriptLines);
            if ($this->updateMode > 0) {
                $choiceId = $messageIdOverride;
                $text = $choiceId . ' - ' . $text;
                $messageIdOverride++;
            }
            $opJump = (int)$opJump;
            $messageParams = [$op1, $op2, $op3, $opJump];
            $messageCode = pack('v', (int)$choiceId) .
                $this->reader->packString($text) .
                $this->reader->packArray($messageParams, 'c', 4, 'intval');

            // Switch file format
            if ($opJump === 7) {
                $messageCode .= $this->reader->packString($pointer);
                $this->compiledSize += strlen($messageCode);
                $code .= $messageCode;
            }

            // Jump format
            if ($opJump === 6) {
                $this->compiledSize += strlen($messageCode) + 4;
                $this->pointers[$pointer] = $messageCode;
            }
        }

        $end = array_shift($scriptLines);
        if ($end !== ');') {
            throw new \Exception('Incorrect choice structure.');
        }
        $this->content = $code;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function compile(array $pointers): string
    {
        if (empty($this->pointers)) {
            return $this->content;
        }
        $code = '';
        foreach ($this->pointers as $label => $messageCode) {
            $pointer = null;
            if ($label === 0) {
                $pointer = 0;
            }
            if (array_key_exists($label, $pointers)) {
                $pointer = $pointers[$label];
            }
            if ($pointer === null) {
                throw new Exception('Unable to find pointer for ' . static::OPCODE . ' and label ' . $label);
            }
            $code .= $messageCode . pack('V', $pointer);
        }
        return $this->content . $code;
    }

    public function validate(?string $params, array &$dataSource, FilesValidator $filesValidator): ?string
    {
        // Skip some lines
        $textLine = array_shift($dataSource);
        while($textLine !== ');') {
            $textLine = array_shift($dataSource);
        }
        return null;
    }
}