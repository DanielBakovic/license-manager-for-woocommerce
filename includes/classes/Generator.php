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
     * Prefix
     *
     * @since 1.0.0
     */
    const PREFIX = '';

    /**
     * Suffix
     *
     * @since 1.0.0
     */
    const SUFFIX = '';

    /**
     * Generator Constructor.
     */
    public function __construct()
    {
        add_filter('LM_create_license_keys', array($this, 'createLicenseKeys'), 10);
        add_filter('LM_generate_license_string', array($this, 'generateLicenseString'), 10);
    }

    /**
     * Generate a single license string
     *
     * @since 1.0.0
     *
     * @param string $args['charset']      - Character map from which the license will be generated
     * @param int    $args['chunks']       - Number of chunks
     * @param int    $args['chunk_length'] - The length of an individual chunk
     * @param string $args['separator']    - Separator used
     * @param string $args['prefix']       - Prefix used
     * @param string $args['suffix']       - Suffix used
     * @param int    $args['expires_in']   - Number of days in which the license key expires
     *
     * @todo Retrieve the default parameters from the user settings.
     *
     * @return string
     */
    public function generateLicenseString($args) {
        if ($args['charset']      == null) $args['charset']      = self::CHARSET;
        if ($args['chunks']       == null) $args['chunks']       = self::CHUNKS;
        if ($args['chunk_length'] == null) $args['chunk_length'] = self::CHUNK_LENGTH;
        if ($args['separator']    == null) $args['separator']    = self::SEPARATOR;
        if ($args['prefix']       == null) $args['prefix']       = self::PREFIX;
        if ($args['suffix']       == null) $args['suffix']       = self::SUFFIX;

        $charset_length = strlen($args['charset']);
        $license_string = $args['prefix'];

        // loop through the chunks
        for ($i=0; $i < $args['chunks']; $i++) {
            // add n random characters from $args['charset'] to chunk, where n = $args['chunk_length']
            for ($j = 0; $j < $args['chunk_length']; $j++) {
                $license_string .= $args['charset'][rand(0, $charset_length - 1)];
            }
            // do not add the separator on the last iteration
            if ($i < $args['chunks'] - 1) {
                $license_string .= $args['separator'];
            }
        }

        $license_string .= $args['suffix'];

        return $license_string;
    }

    /**
     * Bulk create license keys, if possible for given parameters.
     *
     * @since 1.0.0
     *
     * @param int    $args['amount']       - Number of license keys to be generated.
     * @param string $args['charset']      - Character map from which the license will be generated.
     * @param int    $args['chunks']       - Number of chunks.
     * @param int    $args['chunk_length'] - The length of an individual chunk.
     * @param string $args['separator']    - Separator used.
     * @param string $args['prefix']       - Prefix used.
     * @param string $args['suffix']       - Suffix used.
     * @param int    $args['expires_in']   - Number of days in which the license key expires.
     *
     * @todo Improve the parameter input validation.
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

        // check if it's possible to create as many combinations using the input args
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
                array(
                    'charset'      => $args['charset'],
                    'chunks'       => $args['chunks'],
                    'chunk_length' => $args['chunk_length'],
                    'separator'    => $args['separator'],
                    'prefix'       => $args['prefix'],
                    'suffix'       => $args['suffix'],
                )
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
                        'separator'    => $args['separator'],
                        'prefix'       => $args['prefix'],
                        'suffix'       => $args['suffix']
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