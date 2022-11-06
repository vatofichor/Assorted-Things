<?php
/* File.php
* -----------------------------------
* GitHub: py-seb
* Author: Sebastian Mass
* I made this php class for an old project to handle some file operations.
* Could use some updates, optimizations, and a little love.
*
* HOW TO USE:
* **Note that these methods will not work with hidden files!
* createDir()
*   Method to create a directory. Parameters include the directory path and optional
*   umask value. The default umask is full read, write, and execute for everyone.
*   Returns false if directory exist or cannot create directory, true if directory is created.
* delete()
*   Method which can delete a single, multiple, or all files within a given directory.
*     Delete Single File:
*     Delete Multiple Files:
*     Delete Recursively:
* rename()
*	Method to rename files, will not work on directories.
*		Rename All Files Within Directory:
*		Rename Multiple Files With Common Name: (This will rename the files with an incrementing suffix in brackets)
*		Rename Multiple Files With Individually Provided Names:
*		Rename Single File:
* TO DO:
* -Revisit move method.
* -Finish and test Exception handling method.
* -Multiple file rename with custom prefix instead of incre. no.
*/
	class File {
		public static function createDir($path, $permissionMask = 0756){
			if(self::checkExist($path, 'dir')){
				self::error('Directory ' . $path . 'already exist.');
			} else {
				if(!mkdir($path, $permissionMask)){
					self::error('Failed to create directory ' . $path . '.');
				}
			}
		}

		public static function delete($path, $files){
			if($files === '*'){ //DELETE ALL
				foreach(glob("{$path}/*", GLOB_BRACE) as $file){ //**grab all files/folders to run against
					if(self::checkExist($file, 'dir')){ //**object is folder, start recursive delete on contents
						self::delete($file, '*');
					} else {
						if(!self::delete(dirname($file), basename($file))){
							self::error('Failed to delete file\(s\)');
							}
						}
					}
					if(count(glob("{$path}/*.*", GLOB_BRACE)) === 0){ //**directory is empty so rm it
						if(!rmdir($path)){
							self::error('Failed to delete directory');
						}
					}
			} elseif(is_array($files)) { //**delete multiple provided files/folders
				foreach($files as $file){
					self::delete($path, $file);
					/*List files failed to remove to output to error reporting.*/
				}
			}
			else { //**delete a single file
				if(self::checkExist("{$path}/{$files}")){
					if(!unlink("{$path}/{$files}")){
						self::error('Failed to delete file.');
					}
				}
			}
		}

		public static function rename($path, $files, $newName){
			$x = 0;
			if($files === '*'){ //**rename all files in directory w/ common name but append incre. no.
				$globfiles = array_map('basename', glob("{$path}/*.*", GLOB_BRACE));
				self::rename($path, $globfiles, $newName);
			}
			elseif(is_array($files)){ //**rename multiple files
				if(!is_array($newName)){ //**rename each file w/ common name but append incre. no.
					foreach($files as $file){
						$x++;
						if(!self::rename($path, $file, $newName . "[$x]")){
							/*List files failed to rename to output to error reporting.*/
							}
					}
				} else { //**rename each file w/ individually provided names
					foreach($files as $file){
						self::rename($path, $file, $newName[$x]);
						/*List files failed to remove to output to error reporting.*/
						$x++;
					}
				}
			}
			else{ //**rename single file
				if(self::checkExist("{$path}/{$files}")){
					$ext = pathinfo("{$path}/{$files}", PATHINFO_EXTENSION);
					if(!rename("{$path}/{$files}", "{$path}/{$newName}.{$ext}")){
						self::error('Failed to rename file');
						}
				}
			}
		}

		/*Move a single or multiple files, include file directory path, target directory, and file name or an
		 * array of file names.
		 */
		public static function move($path, $target, $files, $delete = false){
			if(!$delete){
				if(is_array($files)){
					foreach($files as $file){
						self::move($path, $target, $file);
					}
				} else {
					if(!copy("{$path}/{$files}", "{$target}/{$files}")){
						//FAIL_MOVE_FILE
					}
				}
			} else {
				if(is_array($files)){
					foreach($files as $file){
						self::move($path, $target, $file, true);
					}
				} else {
					if(!rename("{$path}/{$files}", "{$target}/{$files}")){
						//FAIL_MOVE_FILE
					}
				}
			}
		}

		//Check to see if a directory or file exist then return result. Set $type to folder to check for directory.
		private static function checkExist($pathfile, $type = NULL){
			if($type === 'dir'){
				return is_dir($pathfile);
			} else {
				return is_file($pathfile);
			}
		}

		/*EXCEPTION HANDLING*/
		protected static function error($e){
			throw new Exception($e);
		}

	}
?>
