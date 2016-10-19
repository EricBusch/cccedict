<?php

namespace CcCedict;

/**
 * Class for parsing the CC-CEDICT dictionary
 */
class Parser
{
    /**
     * path/filename to the CC-CEDICT data
     *
     * @var string
     */
    private $filePath;

    /**
     * options for Entry report
     *
     * @var array
     */
    private $options = [];

    /**
     * Sets the path/filename containing the raw uncompressed CC-CEDICT data
     *
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * set options with which to configure the report from the Entry object
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Reads lines from the file, separates any meta-data, and parses the file
     *
     * @return array
     */
    public function parse()
    {
        $parsedLines = [];
        $skippedLines = [];

        $fp = fopen($this->filePath, 'r');

        if ($fp) {
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                if ($line !== '' || strpos($line, '#') !== 0) {
                    $parsedLine = $this->parseLine($line);
                    if ($parsedLine) {
                        $parsedLines[] = $parsedLine;
                    } else {
                        $skippedLines[] = $line;
                    }
                }
            }
            return [
                'numSkipped' => count($skippedLines),
                'numParsed' => count($parsedLines),
                'parsedLines' => $parsedLines,
                'skippedLines' => $skippedLines,
            ];
        } else {
            throw new \Exception('Could not open file for parsing: ' . $this->filePath);
        }
    }

    /**
     * parses a single line from the file, checking to see it meets basic dictionary spec
     *
     * @param  string $line A line from the CC-CEDICT file
     *
     * @return false|array
     */
    private function parseLine($line)
    {
        $line = trim($line);

        // Traditional Simplified [pin1 yin1] /English equivalent 1/equivalent 2/
        // 中國 中国 [Zhong1 guo2] /China/Middle Kingdom/
        if (preg_match('#(.+) (.+) \[(.+)\] /(.*)/#', $line, $match)) {
            $entry = new Entry();
            $entry->setData($match);

            if (count($this->options)) {
                return $entry->getOptional($this->options);
            } else {
                return $entry->getBasic();
            }
        } else {
            return false;
        }
    }
}
