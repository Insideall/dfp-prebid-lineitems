<?php

// for more info see:
// https://github.com/FriendsOfPHP/PHP-CS-Fixer#usage
// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/UPGRADE.md

$finder = PhpCsFixer\Finder::create()
	->in(__DIR__)
;

return PhpCsFixer\Config::create()
	->setRules([
		'@Symfony' => true,
		'array_syntax' => ['syntax' => 'short'],
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
;
