<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query;

enum DbalOperator: string
{
    case AND = 'and';
    case OR = 'or';
    case EQ = 'eq';
    case NEQ = 'neq';
    case LT = 'lt';
    case LTE = 'lte';
    case GT = 'gt';
    case GTE = 'gte';
    case LIKE = 'like';
    case NOT_LIKE = 'notLike';
    case IN = 'in';
    case NOT_IN = 'notIn';
    case IS_NOT_NULL = 'isNotNull';
    case IS_NULL = 'isNull';

    public function isConjunction(): bool
    {
        $operators = [
            self::AND,
            self::OR,
        ];

        return in_array($this, $operators);
    }

    public function isComparison(): bool
    {
        $operators = [
            self::EQ,
            self::NEQ,
            self::LT,
            self::LTE,
            self::GT,
            self::GTE,
            self::LIKE,
            self::NOT_LIKE,
            self::IN,
            self::NOT_IN,
        ];

        return in_array($this, $operators);
    }

    public function isComparisonWithoutParam(): bool
    {
        $operators = [
            self::IS_NOT_NULL,
            self::IS_NULL,
        ];

        return in_array($this, $operators);
    }
}
