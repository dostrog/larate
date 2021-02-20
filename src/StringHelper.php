<?php
declare(strict_types=1);

namespace Dostrog\Larate;

use RuntimeException;
use SimpleXMLElement;
use Throwable;

final class StringHelper
{
    /**
     * Transforms an XML string to an element.
     */
    public static function xmlToElement(string $string): SimpleXMLElement
    {
        $internalErrors = libxml_use_internal_errors(true);

        try {
            // Allow XML to be retrieved even if there is no response body
            $xml = new SimpleXMLElement($string ?: '<root />', LIBXML_NONET);

            libxml_use_internal_errors($internalErrors);
        } catch (Throwable $throwable) {
            libxml_use_internal_errors($internalErrors);

            throw new RuntimeException(trans('larate::error.badxml', ['message' => $throwable->getMessage()]), $throwable->getCode(), $throwable);
        }

        return $xml;
    }
}
