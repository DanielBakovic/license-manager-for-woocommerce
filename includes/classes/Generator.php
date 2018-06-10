<?php

namespace LicenseManager\Classes;

/**
 * LicenseManager Generator.
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Generator class.
 */
class Generator
{
    /**
     * The character map, from which the license will be generated
     *
     * @since 1.0.0
     */
    const CHARSET = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';

    /**
     * The number of chunks
     *
     * @since 1.0.0
     */
    const CHUNKS = 2;

    /**
     * The length of each individual chunk
     *
     * @since 1.0.0
     */
    const CHUNK_LENGTH = 5;

    /**
     * The chunk separator character
     *
     * @since 1.0.0
     */
    const SEPARATOR = '-';

    /**
     * Generator Constructor.
     */
    public function __construct()
    {
        add_filter('LM_create_license_keys', array($this, 'createLicenseKeys'), 10, 1);
        add_filter('LM_generate_license_string', array($this, 'generateLicenseString'), 10, 4);
    }

    /**
     * Generate a single license string
     *
     * @since 1.0.0
     *
     * @param string $charset      - Character map from which the license will be generated
     * @param int    $chunks       - Number of chunks
     * @param int    $chunk_length - The length of an individual chunk
     * @param string $separator    - Separator used
     *
     * @return string
     */
    public function generateLicenseString(
        $charset      = self::CHARSET,
        $chunks       = self::CHUNKS,
        $chunk_length = self::CHUNK_LENGTH,
        $separator    = self::SEPARATOR
    ) {
        if ($charset      == null) $charset      = self::CHARSET;
        if ($chunks       == null) $chunks       = self::CHUNKS;
        if ($chunk_length == null) $chunk_length = self::CHUNK_LENGTH;
        if ($separator    == null) $separator    = self::SEPARATOR;

        $charset_length = strlen($charset);
        $license_string = '';

        // loop through the chunks
        for ($i=0; $i < $chunks; $i++) {
            // generate n characters, where n = $chunk_length
            for ($j = 0; $j < $chunk_length; $j++) {
                $license_string .= $charset[rand(0, $charset_length - 1)];
            }
            // do not add the separator on the last iteration
            if ($i < $chunks - 1) {
                $license_string .= $separator;
            }
        }

        return $license_string;
    }

    /**
     * Bulk create license keys, if possible for given parameters.
     *
     * @since 1.0.0
     *
     * @param int    $args['amount']       - Number of license keys to be generated
     * @param string $args['charset']      - Character map from which the license will be generated
     * @param int    $args['chunks']       - Number of chunks
     * @param int    $args['chunk_length'] - The length of an individual chunk
     * @param string $args['separator']    - Separator used
     *
     * @return array
     */
    public function createLicenseKeys($args)
    {
        // check if the amount is properly set
        if (isset($args['amount']) && is_numeric($args['amount'])) {
            $amount = $args['amount'];
        } else {
            $amount = 1;
        }

        // check if it's possible to create as many variations using the input args
        $unique_characters = count(array_unique(str_split($args['charset'])));
        $max_possible_keys = pow($unique_characters, $args['chunks'] * $args['chunk_length']);

        if ($amount > $max_possible_keys) {
            $e = new \Exception(
                __('It\'s not possible to generate that many keys with the given parameters, there are not enough combinations. Please review your inputs.', 'lima'),
                1
            );
            Logger::exception($e);
            Logger::exception($args);
            throw $e;

            return;
        }

        // Generate the license strings
        for ($i=0; $i < $amount; $i++) { 
            $args['licenses'][] = apply_filters(
                'LM_generate_license_string',
                $args['charset'],
                $args['chunks'],
                $args['chunk_length'],
                $args['separator']
            );
        }

        // Remove duplicate entries from the array
        $args['licenses'] = array_unique($args['licenses']);

        // check if any licenses have been removed
        if (count($args['licenses']) < $amount) {
            // regenerate removed license keys, repeat until there are no duplicates
            while (count($args['licenses']) < $amount) {
                $args = $this->createLicenseKeys(
                    array(
                        'amount'       => $amount - count($args['licenses']),
                        'licenses'     => $args['licenses'],
                        'charset'      => $args['charset'],
                        'chunks'       => $args['chunks'],
                        'chunk_length' => $args['chunk_length'],
                        'separator'    => $args['separator']
                    )
                );
            }
        }

        // sort and reindex the array
        sort($args['licenses']);
        $args['licenses'] = array_values($args['licenses']);
        $args['amount']   = $amount;

        return $args;
    }
}