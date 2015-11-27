<?php

require_once("analyze_local_folder.php");

echo "Analysis of Mined repos with no tool support\n\r";
echo "Creates log files";

function AnalyzeProject($a_path, $a_name) {

	$project = simplexml_load_file($a_path . "/" . $a_name);
	//print_r($pr->commit[0]);
	foreach ($project as $commit) {
		//print_r((string)$commit['commit_name']);
		$repo = (string)$commit['commit_number'];
		$repo = $a_path . "/" . $repo;
		$hash = (string)$commit['commit_name'];

		echo "Performing analysiz on: " . $repo . PHP_EOL;
		PerformAnalysis($repo, $hash);
		sleep(2);
	}

}

/*AnalyzeProject("C:/hObbE/projekt/rulzor/mined/2", "Project-Webbutveckling-med-PHP.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/4", "QuizProject.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/10", "1dv408-Projekt.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/11", "php_project.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/13", "Webbutveckling-med-PHP.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/17", "PHP_Projekt.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/21", "1DV408_Projekt.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/22", "jj222kc_webbutveckling_med_php.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/23", "Webbutveckling_med_PHP_ek222mw.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/29", "1DV408-project.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/30", "1DV408-Projekt.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/31", "PHP-Project.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/34", "projektet-i-PHP.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/35", "MySpotifyPlaylists.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/36", "PHP-Projekt.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/37", "1DV408_Projekt_as223my.xml");
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/38", "Skola-PHP.xml");*/
AnalyzeProject("C:/hObbE/projekt/rulzor/mined/41", "php-projekt.xml");


?>