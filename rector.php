<?php

declare(strict_types=1);

use craft\rector\SetList as CraftSetList;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {

    // PHP 8.0
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->import(LevelSetList::UP_TO_PHP_80);

    // Misc
    $rectorConfig->import(CraftSetList::CRAFT_CMS_40);
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::TYPE_DECLARATION);
    $rectorConfig->import(SetList::CODE_QUALITY);
    $rectorConfig->import(SetList::CODING_STYLE);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_90);

    $rectorConfig->skip([
        \Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector::class,
        \Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector::class,
        \Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class,
        \Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector::class,
        \Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class,
        \Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector::class,
        \Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector::class,
        \Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector::class,
        \Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector::class,
        \Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector::class,
        \Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector::class,
        \Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector::class,
        \Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class,
        \Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector::class,
        \Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector::class,
        \Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class,
        \Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector::class
    ]);


};





