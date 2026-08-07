<?php

declare(strict_types=1);

namespace App\Enum;

enum HttpRedirectCode: int
{
    // case MULTIPLE_CHOICES = 300; // Choices are listed in an HTML page in the body. Machine-readable choices are encouraged to be sent as Link headers with rel=alternate.
    case MOVED_PERMANENTLY = 301; // Reorganization of a website.
    case FOUND = 302; // The Web page is temporarily unavailable for unforeseen reasons.
    // case SEE_OTHER = 303; // Used to redirect after a PUT or a POST, so that refreshing the result page doesn't re-trigger the operation.
    // case NOT_MODIFIED = 304; // Sent for revalidated conditional requests. Indicates that the cached response is still fresh and can be used.
    // case TEMPORARY_REDIRECT = 307; // The Web page is temporarily unavailable for unforeseen reasons. Better than 302 when non-GET operations are available on the site.
    // case PERMANENT_REDIRECT = 308; // Reorganization of a website, with non-GET links/operations.
    // case FORBIDDEN = 403; // Server understood the request but refused to process it.
    // case NOT_FOUND = 404; // Links that lead to a 404 page are often called broken or dead links and can be subject to link rot.

    /**
     * Returns the human-readable HTTP status label.
     */
    public function getLabel(): string
    {
        return match ($this) {
            // self::MULTIPLE_CHOICES => '300 Multiple Choices',
            self::MOVED_PERMANENTLY => '301 Moved Permanently',
            self::FOUND => '302 Found',
            // self::SEE_OTHER => '303 See Other',
            // self::NOT_MODIFIED => '304 Not Modified',
            // self::TEMPORARY_REDIRECT => '307 Temporary Redirect',
            // self::PERMANENT_REDIRECT => '308 Permanent Redirect',
            // self::FORBIDDEN => '403 Forbidden',
            // self::NOT_FOUND => '404 Not Found',
        };
    }

    /**
     * Returns all HTTP redirect code labels.
     *
     * @return array<int, string>
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->getLabel();
        }

        return $labels;
    }
}
