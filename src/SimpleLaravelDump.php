<?php

namespace AmidPro\SimpleLaravelDump;

use Illuminate\Console\Command;

class SimpleLaravelDump extends Command
{
	
    private $dump_path;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "db:dump
    {--db_name= : DB name, default is .env value}
    {--db_user= : DB user, default is .env value}
    {--db_pass= : DB password, default is .env value}
    {--db_host= : DB host, default is .env value}
    {--db_port= : DB port, default is .env value}
    {--db_connection= : DB connection, default is .env value}
    {--dump_path= : Absolute path with trailing slash, default: %laravel_directory%" . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "dump" . DIRECTORY_SEPARATOR . "}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple dump of MySql/Sqlite/PostgreSql databases';

    /**
    * Create a new command instance.
    *
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
        $this->dump_path = database_path() . DIRECTORY_SEPARATOR . 'dump' . DIRECTORY_SEPARATOR;
    }

    /**
     * Check dump
     * 
     * @return boolean
     */
	private function checkDumpFile($path)
	{
		$found = false;
		
		$file = fopen($path, "r");

		if ($file){
			
			$line = 0;
			
			while (($string = fgets($file)) !== false) {
				
				if (stripos($string, "create") !== false){
					$found = true;
					break;
				}
				
				$line++;
				
				if ($line >= 70){
					break;
				}
			}
		
			fclose($file);			
		}
	
		return $found;
	}

    /**
     * Parse result
     * 
     * @return string
     */
    private function resultHandler($result, $code)
    {
        $result = trim($result);
        if ($code == 0 && $result != ""){
            return $result;
        }
        return null;
    }

    /**
     * Return command result
     * 
     * @return string
     */
    private function getExecResult($command)
    {

        if (function_exists('exec')){
            $out = null;
            $code = null;
            $result = exec($command, $out, $code);
            return $this->resultHandler($result, $code);
        }

        if (function_exists('system')){
			ob_start();
            $code = null;
            $result = system($command, $code);
			ob_clean();
            return $this->resultHandler($result, $code);
        }

        if (function_exists('shell_exec')){
            $result = shell_exec($command);
            $code = (!$result) ? 1 : 0;
            return $this->resultHandler($result, $code);
        }

        if (function_exists('passthru')){
            ob_start();
            passthru($command, $code);
            $result = ob_get_contents();
            ob_end_clean();
            return $this->resultHandler($result, $code);
        }

        return null;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		
        $db_name = $this->option('db_name') ?: env('DB_DATABASE');
        $db_user = $this->option('db_user') ?: env('DB_USERNAME');
        $db_password = $this->option('db_pass') ?: env('DB_PASSWORD');
        $db_port = $this->option('db_port') ?: env('DB_PORT');
        $db_connection = $this->option('db_connection') ?: env('DB_CONNECTION');
        $db_host = $this->option('db_host') ?: env('DB_HOST');
        $dump_path = $this->option('dump_path') ?: $this->dump_path;


        if (!is_dir($dump_path)){
            if (!mkdir($dump_path)){
                $this->error('Error create dir');
                return 1;
            }
            file_put_contents($dump_path . '.gitignore', null);
        }

        $dump_name = $dump_path . date('d_m_Y_H_i_s.') . rand(100, 999) . '.dump.sql';

        
        if ($db_connection == 'mysql'){

            $mysqldump = $this->getExecResult('which mysqldump');
            if (!$mysqldump || $mysqldump == ""){
                $this->error('mysqldump not found');
                return 1;
            }

            $command = sprintf('%s -u%s -p%s -P %d %s > %s', $mysqldump, $db_user, $db_password, $db_port, $db_name, $dump_name);
            $this->getExecResult($command);

        }
        else if ($db_connection == 'sqlite'){

            $sqlite3 = $this->getExecResult('which sqlite3');
            if (!$sqlite3 || $sqlite3 == ""){
                $this->error('sqlite3 not found');
                return 1;
            }

            $command = sprintf('%s %s .dump > %s', $sqlite3, $db_name, $dump_name);
			$this->getExecResult($command);
			
        }
        else if ($db_connection == 'pgsql'){

            $pg_dump = $this->getExecResult('which pg_dump');
            if (!$pg_dump || $pg_dump == ""){
                $this->error('pg_dump not found');
                return 1;
            }

            $command = sprintf('%s -U %s -h %s %s -f %s', $pg_dump, $db_user, $db_host, $db_name, $dump_name);
            $this->getExecResult($command);
        }
        else {
            $this->error('Connection not supported');
            return 1;
        }


        if (!is_file($dump_name)){
            $this->error('File error');
            return 1;
        }


        if (filesize($dump_name) < 1){
            unlink($dump_name);
            $this->error('Dump error size');
            return 1;
        }
		
		if (!$this->checkDumpFile($dump_name)){
            unlink($dump_name);
            $this->error('Dump error instructions');
            return 1;
		}

        
        $this->info('Dump created: ' . $dump_name);
        return 0;
    }

}