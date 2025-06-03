<?php

namespace Helper;

/**
 * FastBuffer class provides an efficient way to process array elements sequentially,
 * avoiding the performance overhead of array_shift for large arrays.
 */
class FastBuffer
{
    /**
     * @var array The internal array buffer.
     */
    public array $buffer;

    /**
     * @var int The current read offset in the buffer.
     */
    public int $offset;

    /**
     * @var int The total length of the buffer.
     */
    public int $length;

    /**
     * Constructor.
     *
     * @param array $data The array to be buffered.
     */
    public function __construct(array $data)
    {
        $this->buffer = array_values($data); // Re-index to ensure numeric, sequential keys
        $this->offset = 0;
        $this->length = count($data);
    }

    /**
     * Returns value at the current pointer, in case you need to read in advance for some validations.
     * @return int
     */
    public function current(): int
    {
        return $this->buffer[$this->offset];
    }

    /**
     * Shifts an element off the beginning of the buffer.
     * This method is similar to array_shift but operates by incrementing an internal offset,
     * making it more efficient for large arrays as it avoids re-indexing.
     *
     * @return int|null The shifted element, or null if the buffer is empty.
     */
    public function shift(): ?int
    {
        if ($this->offset < $this->length) {
            return $this->buffer[$this->offset++];
        }
        return null;
    }

    /**
     * Returns the number of remaining elements in the buffer.
     *
     * @return int The number of elements left to be shifted.
     */
    public function count(): int
    {
        return $this->length - $this->offset;
    }

    /**
     * Checks if the buffer is empty.
     *
     * @return bool True if the buffer has no more elements, false otherwise.
     */
    public function isEmpty(): bool
    {
        return $this->length === $this->offset;
    }

    /**
     * Resets the buffer's read offset to the beginning.
     */
    public function reset(): void
    {
        $this->offset = 0;
    }

    /**
     * Returns the entire remaining buffer as an array.
     *
     * @return array The remaining elements in the buffer.
     */
    public function getRemaining(): array
    {
        return array_slice($this->buffer, $this->offset);
    }

    /**
     * Reads a null-terminated string from the buffer efficiently.
     *
     * @return string The read string.
     */
    public function readString(): string
    {
        $startOffset = $this->offset;
        $endOffset = $this->offset;

        // Find the null terminator or end of buffer
        while ($endOffset < $this->length && $this->buffer[$endOffset] !== 0) {
            $endOffset++;
        }

        // Extract the relevant portion of the buffer
        $stringBytes = array_slice($this->buffer, $startOffset, $endOffset - $startOffset);

        // Convert bytes to characters and join them
        $result = implode('', array_map('chr', $stringBytes));

        // Update the offset to after the null terminator (if found) or end of buffer
        $this->offset = $endOffset + ($endOffset < $this->length && $this->buffer[$endOffset] === 0 ? 1 : 0);

        return $result;
    }

    /**
     * Reads a fixed-length string from the buffer.
     *
     * @param int $length The number of bytes to read.
     * @return string The read string.
     */
    public function readFixedLengthString(int $length): string
    {
        if ($this->offset + $length > $this->length) {
            // Not enough bytes left in the buffer
            $length = $this->length - $this->offset;
        }

        $stringBytes = array_slice($this->buffer, $this->offset, $length);
        $result = implode('', array_map('chr', $stringBytes));
        $this->offset += $length;

        return $result;
    }

    /**
     * Unshifts bytes into the buffer by overwriting content before the current cursor.
     * The offset is moved backward by the number of unshifted bytes.
     * The total length of the buffer remains unchanged.
     *
     * @param array $bytes The array of bytes to unshift.
     * @throws \RangeException If the operation would result in a negative offset.
     */
    public function unshift(array $bytes): void
    {
        $numBytes = count($bytes);
        $newOffset = $this->offset - $numBytes;

        if ($newOffset < 0) {
            throw new \RangeException("We assume we are never going to overshoot the beginning of the string.");
        }

        // Overwrite content before the current cursor
        for ($i = 0; $i < $numBytes; $i++) {
            if (isset($bytes[$i])) {
                $this->buffer[$newOffset + $i] = $bytes[$i];
            }
        }

        // Move offset backward
        $this->offset = $newOffset;
        // Length remains unchanged as per requirement
    }
}
