<?php

namespace Mdeskandari\Fstream\Files;

use Mdeskandari\Fstream\Exceptions\FileException;
use Generator;


class FileStream
{
    public const MODES = [
        'R' => 'r', // 'r' 	Open for reading only; place the file pointer at the beginning of the file.
        'R+' => 'r+', // 'r+' 	Open for reading and writing; place the file pointer at the beginning of the file.
        'W' => 'w', // 'w' 	Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
        'W+' => 'w+', // 'w+' 	Open for reading and writing; otherwise it has the same behavior as 'w'.
        'A' => 'a', // 'a' 	Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() has no effect, writes are always appended.
        'A+' => 'a+', // 'a+' 	Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it. In this mode, fseek() only affects the reading position, writes are always appended.
        'X' => 'x', // 'x' 	Create and open for writing only; place the file pointer at the beginning of the file. If the file already exists, the fopen() call will fail by returning false and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
        'X+' => 'x+', // 'x+' 	Create and open for reading and writing; otherwise it has the same behavior as 'x'.
        'C' => 'c', // 'c' 	Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither truncated (as opposed to 'w'), nor the call to this function fails (as is the case with 'x'). The file pointer is positioned on the beginning of the file. This may be useful if it's desired to get an advisory lock (see flock()) before attempting to modify the file, as using 'w' could truncate the file before the lock was obtained (if truncation is desired, ftruncate() can be used after the lock is requested).
        'c+' => 'c+', // 'c+' 	Open the file for reading and writing; otherwise it has the same behavior as 'c'.
        'E' => 'e', // 'e' 	Set close-on-exec flag on the opened file descriptor. Only available in PHP compiled on POSIX.1-2008 conform systems.
    ];

    /**
     * this is the file string path
     * @var mixed $path
     */
    private mixed $path;
    
    /**
     * this is the file stream pointer
     * @var mixed $file
     */
    private mixed $file;

    private string $mode;
    private bool $newFlag;

    /**
     * @throws FileException
     */
    public function __construct(mixed $path, bool $create = false)
    {
        if ($create) {
            $this->setMode(self::MODES['X+']);
        }
        $this->newFlag = $create;
        $this->setFile($path);
    }

    /**
     * @return mixed
     */
    public function getFile(): mixed
    {
        return $this->path;
    }

    /**
     * @param mixed $file
     * @return void
     * @throws FileException
     */
    public function setFile(mixed $file): void
    {
        if (!file_exists($file) && !$this->newFlag) {
            throw new FileException("File you are looking for does Not Exists", 1);
        } else if (!file_exists($file) && $this->newFlag) {
            fclose(fopen($file, $this->mode));
        }
        $this->path = $file;
    }


    /**
     * @return void
     */
    private function open(): void
    {
        $this->file = fopen($this->path, $this->mode);
    }

    /**
     * @return void
     */
    private function close(): void
    {
        fclose($this->file);
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return void
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }


    /**
     * @param string $content
     * @return void
     * @throws FileException
     */
    public function write(string $content): void
    {
        $this->processFile(self::MODES['W'], 'fwrite', $content);
    }


    /**
     * @param string $content
     * @return void
     * @throws FileException
     */
    public function append(string $content): void
    {
        $this->processFile(self::MODES['A'], 'fwrite', $content);
    }

    /**
     * @throws FileException
     */
    public function read()
    {
        return $this->processFile(self::MODES['R'], 'fread');
    }

    /**
     * @throws FileException
     */
    public function toArray()
    {
        return $this->processFile(self::MODES['R'], 'file');
    }

    /**
     * @param string $mode
     * @param callable $callback
     * @param string $context
     * @return mixed
     * @throws FileException
     */
    private function processFile(string $mode, callable $callback, string $context = ""): mixed
    {
        $this->setMode($mode);
        $this->open();

        switch ($callback) {
            case 'fwrite' :
                $callback($this->file, $context);
                break;

            case 'fread':
                $tmpValue = $callback($this->file, filesize($this->path));
                $this->close();
                return $tmpValue;
            case 'file':
                $tmpValue = $callback($this->path);
                $this->close();
                return $tmpValue;

            case 'fgets':
                yield from $this->readBy('fgets');
                break;
            case 'fgetc':
                yield from $this->readBy('fgetc');
                break;
            case 'fgetss':
                yield from $this->readBy('fgetss');
                break;

            default:
                throw new FileException('Unexpected match value');
        };

        $this->close();
        return 0;
    }

    /**
     * @param callable $callback
     * @return Generator
     */
    private function readBy(callable $callback): Generator
    {
        while (!feof($this->file)) {
            yield $callback($this->file);
        }
    }

    /**
     * @return Generator
     * @throws FileException
     */
    public function readLine(): Generator
    {
        return $this->processFile(self::MODES["R"], 'fgets');
    }

    /**
     * @return Generator
     * @throws FileException
     */
    public function readLineStrip(): Generator
    {
        return $this->processFile(self::MODES["R"], 'fgetss');
    }

    /**
     * @return Generator
     * @throws FileException
     */
    public function readChars(): Generator
    {
        return $this->processFile(self::MODES["R"], 'fgetc');
    }

    public function __destruct()
    {
        fclose($this->file);
        unset($this->mode);
        unset($this->path);
        unset($this->file);
    }


}