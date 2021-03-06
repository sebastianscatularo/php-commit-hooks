<?php
/**
 * Copyright (c) <2009>, all contributors
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, 
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * - Neither the name of the project nor the names of its contributors may 
 *   be used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 *
 * This software is provided by the copyright holders and contributors "as is" 
 * and any express or implied warranties, including, but not limited to, the 
 * implied warranties of merchantability and fitness for a particular purpose 
 * are disclaimed. in no event shall the copyright owner or contributors be 
 * liable for any direct, indirect, incidental, special, exemplary, or 
 * consequential damages (including, but not limited to, procurement of 
 * substitute goods or services; loss of use, data, or profits; or business 
 * interruption) however caused and on any theory of liability, whether in 
 * contract, strict liability, or tort (including negligence or otherwise) 
 * arising in any way out of the use of this software, even if advised of the 
 * possibility of such damage.
 *
 * @package php-commit-hooks
 * @version $Revision$
 * @license http://www.opensource.org/licenses/bsd-license.html New BSD license
 */

/**
 * Check 
 * 
 * @package php-commit-hooks
 * @version $Revision$
 * @license http://www.opensource.org/licenses/bsd-license.html New BSD license
 */
abstract class pchCheck
{
    /**
     * Validate the current check
     *
     * Validate the check on the specified repository. Returns an array of 
     * found issues.
     * 
     * @param pchRepository $repository 
     * @return void
     */
    abstract public function validate( pchRepository $repository );

    /**
     * Returns an array of chanegd files
     *
     * Returns an array with the names of all files wich have been changed in 
     * the specified transaction / revision.
     * 
     * @param pchRepository $repository 
     * @return array
     */
    protected function getChangedFiles( pchRepository $repository )
    {
        $process = $repository->buildSvnLookCommand( 'changed' );
        $process->execute();

        $files   = preg_split( '(\r\n|\r|\n)', trim( $process->stdoutOutput ) );

        $filtered = array();
        foreach ( $files as $file )
        {
            if ( !preg_match( '(^[AMU]\s+(?P<filename>.*)$)', $file, $match ) )
            {
                continue;
            }

            $filtered[] = $match['filename'];
        }

        return $filtered;
    }

    /**
     * Get file contents as stream
     *
     * Return the contents of the specified file as a PHP stream
     * 
     * @param pchRepository $repository 
     * @param string $file 
     * @return resource
     */
    protected function getFileContents( pchRepository $repository, $file )
    {
        $fileContents = $repository->buildSvnLookCommand( 'cat' );
        $fileContents->argument( $file );
        $fileContents->execute();

        if ( !in_array( 'pchString', stream_get_wrappers() ) )
        {
            stream_wrapper_register( 'pchString', 'pchStringStream' );
        }

        $stream = fopen( 'pchString://', 'w' );
        fwrite( $stream, $fileContents->stdoutOutput );
        fseek( $stream, 0 );
        return $stream;
    }
}

