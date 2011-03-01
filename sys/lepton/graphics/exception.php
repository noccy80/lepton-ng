<?php

class GraphicsException extends BaseException {
    const ERR_GENERIC = 0;
    const ERR_FILE_NOT_FOUND = 1;
    const ERR_META = 2;
    const ERR_BAD_COLOR = 3; /// Bad color value
    const ERR_BAD_FONT = 4; /// Bad font
    const ERR_LOAD_FAILURE = 5;
    const ERR_SAVE_FAILURE = 6;
}
