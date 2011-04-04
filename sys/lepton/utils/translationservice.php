<?php

interface ITranslationService {
	function __construct($fromlang,$tolang);
	function translate($string);
}

abstract class TranslationService implements ITranslationService { }
