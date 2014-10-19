<?php

require_once("gitapi.php");

class TrackedIssues {
	private $m_issues;
	private $m_fileName;
	private $m_log;
	private $m_git;

	public function __construct($a_fileName, LogFile $a_log, GitIssuesApiWrapper &$a_git) {
		$this->m_fileName = $a_fileName;
		$this->m_log = $a_log;
		$this->m_git = $a_git;
		$this->m_issues = json_decode(@file_get_contents($a_fileName), true);
		if (!is_array($this->m_issues)) {
			$this->m_log->Log("No Old Issues Found");
				$this->m_issues = array();
		}
	}

	public function OpenIssue($a_issueKey, $a_problemHeadline, $a_problemText, $a_currentIssue) {
		if (isset($this->m_issues[$a_issueKey]) && $this->m_issues[$a_issueKey]["number"] >= 0) {

			$gitIssueId = $this->m_issues[$a_issueKey]["number"];

			$result = $this->m_git->GetIssue($gitIssueId);
			$issue = json_decode($result);
			if (isset($issue->number)) {
				if ($issue->state == "closed") {
					// reopen if it's not a wontfix

					$wontfix = false;
					if (isset($issue->labels) && is_array($issue->labels)) {
						foreach ($issue->labels as $label) {
							if ($label->name == "wontfix") {
								$wontfix = true;
								break;
							}
						}
					}

					if (!$wontfix) {
						$this->SetIssue($gitIssueId, $a_problemText, true, "Reopened Issue on GitHub: ", "Error! Could Not Reopen Issue on GitHub: ");
					}
				} else if ($issue->body != $a_problemText) {
					// issue has changed to post a new body
					$this->SetIssue($gitIssueId, $a_problemText, true, "Set Issue Body on GitHub: ", "Error! Could Not Set Issue Body on GitHub: ");
				}
			} else {
				$this->m_log->Log("Error! Could Not Find Issue on GitHub: " . $this->m_git->GetLastUrl());
				$this->m_log->Log("Response fromg GitHub:" . $result);
			}
		} else {
			$result = $this->m_git->PostIssue($a_problemHeadline, $a_problemText);
			$issue = json_decode($result);
			if (isset($issue->number)) {
				$issue = array("number" => $issue->number, "issue" => $a_currentIssue);
				$this->m_log->Log("Created Issue on GitHub: " . $this->m_git->GetLastUrl());
				$this->m_issues[$a_issueKey] = $issue;	
			} else {
				$this->m_log->Log("Error! Could Not Create Issue on GitHub: " . $this->m_git->GetLastUrl());
				$this->m_log->Log("Response fromg GitHub:" . $result);
			}
		}
	}

	public function CloseIssue($a_issueKey) {
		if (isset($this->m_issues[$a_issueKey])) {
			$result = $this->m_git->GetIssue($this->m_issues[$a_issueKey]["number"]);
			$issue = json_decode($result);
			if (isset($issue->number)) {
				if ($issue->state == "open") {
					// close the issue
					$this->SetIssue($issue->number, "", false, "Closed Issue on GitHub: ", "Error! Could Not Close Issue on GitHub: ");

				}
			} else {
				$this->m_log->Log("Error! Could Not Find Issue on GitHub: " . $this->m_git->GetLastUrl());
				$this->m_log->Log("Response fromg GitHub:" . $result);
			}
		}
	}

	private function SetIssue($a_issueId, $a_problemText, $a_isOpen, $a_successLogMessage, $a_errorLogMessage) {
		$result = $this->m_git->SetIssue($a_issueId, $a_problemText, $a_isOpen);
		$issue = json_decode($result);
		if (isset($issue->number)) {
			$this->m_log->Log($a_successLogMessage . $this->m_git->GetLastUrl());
		} else {
			$this->m_log->Log($a_errorLogMessage . $this->m_git->GetLastUrl());
			$this->m_log->Log("Response fromg GitHub:" . $result);
		}
	}

	public function Save() {
		file_put_contents($this->m_fileName, json_encode($this->m_issues));
	}
}


?>