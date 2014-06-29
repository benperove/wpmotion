<?php

/*
functions accessed simply by including the file and calling the function:
$t = test();
var_dump($t);
*/

	function user_agent()
	{
		return base64_decode('V1AgTW90aW9uLw==').
		get_option(base64_decode('d3Btb3Rpb25fcGx1Z2luX3ZlcnNpb24=')).
		base64_decode('OyBodHRwOi8vaW50ZXJuZXRtb3ZpbmcuY28vIEBiZW5wZXJvdmU=');
	}

	function check_environment()
	{
		$commands = array( //1st param - command exists, 2nd permissions to run
			'ssh'       => array( FALSE, FALSE ),
			'scp'       => array( FALSE, FALSE ),
			'sftp'      => array( FALSE, FALSE ),
			'rsync'     => array( FALSE, FALSE ),
			'tar'       => array( FALSE, FALSE ),
			'mysqldump' => array( FALSE, FALSE )
			);

		//setup environment array
		$env = array();
		
		//if command exists, set param1 to true
		foreach ( $commands as $cmd => $val ) 
		{
			$result = apply_filters( 'cmd_exists', $cmd );
			if ( $result ) 
			{
				$commands[$cmd][0] = TRUE;
			}
		}		

		//given existing commands
		foreach ( $commands as $cmd => $val ) 
		{
			if ( $commands[$cmd][0] ) 
			{
				//check to make sure we have permissions to run
				$t = shell_exec( $cmd . ' 2>&1' );
				if ( ! empty( $t ) ) 
				{
					$commands[$cmd][1] = TRUE;
				}
			}
		}

  		//get memory limit
  		$php_memory_limit = ini_get( 'memory_limit' );
  		$php_memory_limit = ( int ) $php_memory_limit;
  		$env['php_memory_limit'] = floatval( $php_memory_limit ); //append to array

		//get memory usage
		$mem_usage = memory_get_usage( true ); 
//		if ( $mem_usage < 1024 ) 
//		{
//			echo '';
//		} 
		if ( $mem_usage < 1048576 ) 
		{
  			$usage = round( $mem_usage/1024, 0 );
  		} 
  		else 
  		{
			$usage = round( $mem_usage/1048576, 0 );
			$env['php_memory_usage'] = $usage; //append to array
  		}

  		//calculate free memory
  		$php_memory_free = $php_memory_limit-$usage;
  		$env['php_memory_free'] = $php_memory_free; //append to array

  		//get free space
  		$free_space = disk_free_space('/');
  		$df = $free_space/1048576;
  		$env['disk_free'] = (int) $df;

  		//get webserver
  		$server = $_SERVER['SERVER_SOFTWARE'];
  		$env['webserver'] = $server;

  		//get webserver user
  		$user = shell_exec('whoami');
  		$env['webserver_user'] = $user;

		//export arrays and save to db
		$commands_export = var_export($commands, true);
		$env_export      = var_export($env, true);
  		update_option( 'wpmotion_commands', $commands_export );
  		update_option( 'wpmotion_environment', $env_export );

  		if ($php_memory_free > 10 && $df > 256)
  		{
  			update_option( 'wpmotion_state', '5' );
			//$wpm_state = get_option( 'wpmotion_state' );
  			return TRUE;
  		}
  		else
  		{
  			return FALSE;			
  		}
 
  	}

