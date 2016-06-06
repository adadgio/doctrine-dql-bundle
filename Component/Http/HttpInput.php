<?php

namespace Adadgio\DoctrineDQLBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

// @todo To comment and document
class HttpInput
{
    private $filter;
    private $sort;
    private $limit;
    private $offset;

    public function __construct(Request $request)
    {
        $this->limit = $this->setLimit($request->query->get('limit'));
        $this->offset = $this->setOffset($request->query->get('offset'));

        $this->sort = $this->explodeSort($request->query->get('sort'));
        $this->filter = $this->explodeFilter($request->query->get('filter'));
    }

    public function getFilter()
    {
        return $this->where;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    private function explodeFilter($queryString)
    {
        $filter = array();
        $parts = explode('AND', $queryString);
        // clean each part found quickly
        $parts = array_filter(array_map('trim', $parts));

        foreach ($parts as $part) {
            $exp = explode(':', $part);

            if (count($exp) === 2) {
                $fld = $exp[0];
                $val = $this->normalizeValueExpression($exp[1]);
                $filter[] = array($fld, $val);
            } else {
                throw new \Exception(sprintf('Query criterium for "%s" must have a value, and look like "alias($OPERATOR).field:value" or "alias.field:value"', $exp[0]));
            }
        }

        return $filter;
    }

    private function explodeSort($sortString)
    {
        $sort = explode(':', $sortString);
        $sort = array_filter(array_map('trim', $sort));

        return $sort;
    }

    private function setLimit($limit)
    {
        if ((int) $limit === 0) {
            return null;
        } else {
            return (int) $limit;
        }
    }

    private function setOffset($offset)
    {
        if (empty($offset)) {
            return 0;
        } else {
            return (int) $offset;
        }
    }

    private function normalizeValueExpression($value)
    {
        if (strpos($value, '[') > -1 && strpos($value, ']') > -1) {
            $language = new ExpressionLanguage();
            return $language->evaluate($value);
        } else {
            return $value;
        }
    }
}
