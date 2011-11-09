<?php

    using('lepton.graphics.drawable');

    /**
     * Code39 barcode font renderer for Lepton Presentation Framework. Will
     * convert and render an alphanumeric font to fit within the bounding
     * box provided to the draw() method of the LpfCanvas.
     *
     * The "binary" mappings were taken from the generator at
     * http://www.sid6581.net/cs/php-scripts/barcode/
     *
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class Code39Renderer extends Drawable {

        /// Character set definition
        private $_charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';
        private $_text = '';

        function __construct($text) {

            $this->_text = $text;

        }

        function draw(Canvas $dest,$x=null,$y=null,$width=null,$height=null) {

            $hi = $dest->getImage();
            $str = '*'.$this->_text.'*';

            if ($x && $y && $width && $height) {
                // Each character is 12 units wide, so let's figure out how wide
                // we should make the output. We add 2 for the padding.
                $outwidth = strlen($str) * 14;
                $unitwidth = (int)($width / $outwidth);
                $rx = 0;
                for($i = 0; $i < strlen($str); $i++) {
                    $charbin = $this->getCharacter($str[$i]);
                    for($j = 0; $j < 9; $j++) {
                        $bw = (($charbin[$j]) == '1')?1:0;
                        imagefilledrectangle($hi,
                                             $x + (($rx) * $unitwidth), $y,
                                             $x + (($rx + $bw + 1) * $unitwidth), $y+$height,
                                             (($j % 2))?0xFFFFFF:0x000000);
                        $rx = $rx + 2 + $bw;
                    }
                    $rx = $rx + 2;
                }
            } else {
                new BadArgumentException();
            }
            
        }

        /**
         * Return the "binary" representation of the character in the Code39
         * barcode. The first digit represents the black bar, the second digit
         * the gap, the third digit the next black bar etc. A 1 will render
         * a wide gap/bar, while a 0 will render a think gap/bar.
         *
         * @param string $char The character
         * @return string The binary representation
         */
        private function getCharacter($char) {
            switch (strtoupper($char)) {
                case ' ': return "011000100";
                case '$': return "010101000";
                case '%': return "000101010";
                case '*': return "010010100"; // * Start/Stop
                case '+': return "010001010";
                case '|': return "010000101";
                case '.': return "110000100";
                case '/': return "010100010";
                case '-': return "010000101";
                case '0': return "000110100";
                case '1': return "100100001";
                case '2': return "001100001";
                case '3': return "101100000";
                case '4': return "000110001";
                case '5': return "100110000";
                case '6': return "001110000";
                case '7': return "000100101";
                case '8': return "100100100";
                case '9': return "001100100";
                case 'A': return "100001001";
                case 'B': return "001001001";
                case 'C': return "101001000";
                case 'D': return "000011001";
                case 'E': return "100011000";
                case 'F': return "001011000";
                case 'G': return "000001101";
                case 'H': return "100001100";
                case 'I': return "001001100";
                case 'J': return "000011100";
                case 'K': return "100000011";
                case 'L': return "001000011";
                case 'M': return "101000010";
                case 'N': return "000010011";
                case 'O': return "100010010";
                case 'P': return "001010010";
                case 'Q': return "000000111";
                case 'R': return "100000110";
                case 'S': return "001000110";
                case 'T': return "000010110";
                case 'U': return "110000001";
                case 'V': return "011000001";
                case 'W': return "111000000";
                case 'X': return "010010001";
                case 'Y': return "110010000";
                case 'Z': return "011010000";
                default:  return "011000100";
            }
        }

    }

?>
