<?php
	
	DeleteClientCronjobs();
	WriteCronFile();
	function WriteCronFile() {
		include('cnf/db.php');
		$z_db_user = $user;
		$z_db_pass = $pass;
		try {	
			$zdbh = new db_driver("mysql:host=localhost;dbname=" . $dbname . "", $z_db_user, $z_db_pass);
		} catch (PDOException $e) {

		}
		$line = "";
        $sql = "SELECT * FROM x_cronjobs WHERE ct_deleted_ts IS NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $sql->execute();
			$line .= "#################################################################################". fs_filehandler::NewLine();
			$line .= "# CRONTAB FOR ZPANEL CRON MANAGER MODULE                                         ". fs_filehandler::NewLine();
			$line .= "# Module Developed by Bobby Allen, 17/12/2009                                    ". fs_filehandler::NewLine();
			$line .= "# Automatically generated by ZPanel " . sys_versions::ShowZpanelVersion()."      ". fs_filehandler::NewLine();
			$line .= "#################################################################################". fs_filehandler::NewLine();
			$line .= "# WE DO NOT RECOMMEND YOU MODIFY THIS FILE DIRECTLY, PLEASE USE ZPANEL INSTEAD!  ". fs_filehandler::NewLine();
			$line .= "#################################################################################". fs_filehandler::NewLine();
			
			if (sys_versions::ShowOSPlatformVersion() == "Windows") {
			$line .= "# Cron Debug infomation can be found in this file here:-                        ". fs_filehandler::NewLine();
			$line .= "# C:\WINDOWS\System32\crontab.txt                                                ". fs_filehandler::NewLine();
			$line .= "#################################################################################". fs_filehandler::NewLine();
			$line .= "".ctrl_options::GetOption('daemon_timing')." ".ctrl_options::GetOption('php_exer')." ".ctrl_options::GetOption('daemon_exer')."". fs_filehandler::NewLine();
			$line .= "#################################################################################". fs_filehandler::NewLine();
			}
			
			$line .= "# DO NOT MANUALLY REMOVE ANY OF THE CRON ENTRIES FROM THIS FILE, USE ZPANEL      ". fs_filehandler::NewLine();
			$line .= "# INSTEAD! THE ABOVE ENTRIES ARE USED FOR ZPANEL TASKS, DO NOT REMOVE THEM!      ". fs_filehandler::NewLine();
			$line .= "#################################################################################". fs_filehandler::NewLine();
            while ($rowcron = $sql->fetch()) {
				$rowclient = $zdbh->query("SELECT * FROM x_accounts WHERE ac_id_pk=" . $rowcron['ct_acc_fk'] . " AND ac_deleted_ts IS NULL")->fetch();
				if ($rowclient && $rowclient['ac_enabled_in'] <> 0){
					$line .= "# CRON ID: ".$rowcron['ct_id_pk'].""											. fs_filehandler::NewLine();
					$line .= "" . $rowcron['ct_timing_vc'] . " " . ctrl_options::GetOption('php_exer') . " " . $rowcron['ct_fullpath_vc'] . "" . fs_filehandler::NewLine();
					$line .= "# END CRON ID: ".$rowcron['ct_id_pk'].""										. fs_filehandler::NewLine();
				}
            }
            
			if (fs_filehandler::UpdateFile(ctrl_options::GetOption('cron_file'), 0777, $line)) {
    	        return true;
	        } else {
    	        return false;
	        }
        }
	}

    function DeleteClientCronjobs() {
		include('cnf/db.php');
		$z_db_user = $user;
		$z_db_pass = $pass;
		try {	
			$zdbh = new db_driver("mysql:host=localhost;dbname=" . $dbname . "", $z_db_user, $z_db_pass);
		} catch (PDOException $e) {

		}
        $sql = "SELECT * FROM x_accounts WHERE ac_deleted_ts IS NOT NULL";
        $numrows = $zdbh->query($sql);
        if ($numrows->fetchColumn() <> 0) {
            $sql = $zdbh->prepare($sql);
            $sql->execute();
            while ($rowclient = $sql->fetch()) {
				$rowcron = $zdbh->query("SELECT * FROM x_cronjobs WHERE ct_acc_fk=" . $rowclient['ac_id_pk'] . " AND ct_deleted_ts IS NULL")->fetch();
				if ($rowcron) {
					$delete = "UPDATE x_cronjobs SET ct_deleted_ts=".time()." WHERE ct_acc_fk=".$rowclient['ac_id_pk']."";
        			$delete = $zdbh->prepare($delete);
            		$delete->execute();
    			}
            }
        }
    }

?>