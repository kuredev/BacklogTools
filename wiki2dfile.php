<?php

//////////////////
//Config Section
$apiKey = "";
$projID = 0;
$dir = "";
$spaceID = "";

//////////////////
//Script
$backlog = new Backlog($spaceID, $apiKey, $projID); 
saveAllWiki2File($backlog);

/**
 * 全てのWikiページを$dir配下に保存
 * １ページ１ディレクトリ
 * ディレクトリ配下に「アタッチメントID＋画像ファイル名」で保存する
 * @param type $backlog
 */
function saveAllWiki2File($backlog){
    global $dir;
    mkdir ($dir);
    $wikiIDs = $backlog->getWikiIds();
    $util = new Util();
    $wikiArray = array();
    $nameArray = array();
    foreach ($wikiIDs as $key => $id){
        $wiki =  $backlog->getWiki($id);
        $name = $util->deleteSlash($wiki->name);
        $content = $wiki->content;
        $attachements = $wiki->attachments;
        mkdir($dir."/".$name);
        
        //テキストと画像を保存
        $util->createFile($dir."/".$name, $name.".txt", $content);
        if($attachements){
            foreach ($attachements as $key => $attach){
                $image = $backlog->getAttachementFile($id, $attach->id);
                $filename = $attach->id."_".$attach->name;
                $util->createFile($dir."/".$name, $filename, $image);
            }
        }
        array_push($nameArray, $name);
    }
    var_dump($nameArray);
}

class Util{
    public static function getTitle($json){
        
    }
    
    public static function convertTitle2TitleName($title){
        
    }
    
    public static function deleteSlash($name_){
        $arr_ = explode(" / ", $name_);
        $arr = explode("/", $arr_[count($arr_)-1]);
        return $arr[count($arr) - 1];
    }
    
    public static function createFile($dir_, $name, $content){
        global $dir;
        var_dump($name);
        
        if(!file_put_contents($dir_."/".$name, $content)){
            if(!file_put_contents($dir."/".$name, $data)){
            }
        }
    }
    
    
}

class Issue{
    private $id;
    private $issueKey;
    private $summary;
    private $description;
    
    public function __construct($id, $issueKey, $summary, $description) {
        $this->id = $id;
        $this->issueKey = $issueKey;
        $this->summary = $summary;
        $this->description = $description;
    }
    
    public function getId(){
        return $this->id;
    }
    
    public function getIssueKey(){
        return $this->issueKey;
    }
    
    public function getSummary(){
        return $this->summary;
    }
    
    public function getDescription(){
        return $this->description;
    }
}

class Wiki{
    private $id;
    private $name;
    private $content;
    private $attachment;
    
    public function __construct($id, $name, $content, $attachment) {
        $this->id = $id;
        $this->name = $name;
        $this->content = $content;
        $this->attachment = $attachment;
    }
    
    public function getId(){
        return $this->id;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getContent(){
        return $this->content;
    }
    
    public function getAttachments(){
        return $this->attachment;
    }
}

class Backlog {
    private $apiKey;
    private $projectId;
    private $header = array(
        "Content-Type: application/x-www-form-urlencoded"
    );

    public function __construct($preURL, $apiKey, $projectId) {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->url = "https://". $preURL . ".backlog.jp";
    }
    
    public function getIssues(){
        $url_ = "/api/v2/issues";
        $data = http_build_query(
                array(
                    "count" => 100,
                    "apiKey" => $this->apiKey,
                )
                );
        $options = array("http" =>
                            array("method" => "GET",
                                        "header" => implode("\r\n", $this->header))
            );
        $contents = file_get_contents($this->url.$url_."?".$data,false,  stream_context_create($options));
        return $contents;
    }
    
    public function getIssuesKeyword($keyword){
        $url_ = "/api/v2/issues";
        $data = http_build_query(
                array(
                    "count" => 100,
                    "apiKey" => $this->apiKey,
                    "keyword" => $keyword
                )
                );
        $options = array("http" =>
                            array("method" => "GET",
                                        "header" => implode("\r\n", $this->header))
            );
        $contents = file_get_contents($this->url.$url_."?".$data,false,  stream_context_create($options));
        return $contents;
    }
    
    public function getIssuesIdsKeyword($keyword){
        $issueIdsArray = array();
        $issues = $this->getIssuesKeyword($keyword);
        $issuesArray = json_decode($issues);
        
        foreach ($issuesArray as $key => $value){
            array_push($issueIdsArray, $value->id);
        }
        return $issueIdsArray;
    }
    
    public function getIssueIds(){
        $issueIdsArray = array();
        $issues = $this->getIssues();
        $issuesArray = json_decode($issues);
        
        foreach ($issuesArray as $key => $value){
            array_push($issueIdsArray, $value->id);
        }
        return $issueIdsArray;
    }
    
    public function getIssue($issueId){
        $url_ = "/api/v2/issues/".$issueId;
        $data = http_build_query(
                array(
                    "count" => 100,
                    "apiKey" => $this->apiKey,
                    "projectIdOrKey"=>$this->projectId
                )
                );
        $options = array("http" =>
                            array("method" => "GET",
                                        "header" => implode("\r\n", $this->header))
            );
        $contents = file_get_contents($this->url.$url_."?".$data,false,  stream_context_create($options));
        return $contents;
    }
    
    /**
     * 
     * @param type $wikiId
     * @return type OBJ
     */
    public function getWiki($wikiId){
        $url_ = "/api/v2/wikis/".$wikiId;

        $data = http_build_query(
                array(
                    "apiKey" => $this->apiKey,
                    "projectIdOrKey"=>$this->projectId
                )
                );
        $options = array("http" =>
                            array("method" => "GET",
                                        "header" => implode("\r\n", $this->header))
            );
        $contents = file_get_contents($this->url.$url_."?".$data,false,  stream_context_create($options));
        return json_decode($contents);
        
    }

    /**
     * 
     * @return array
     */
    public function getWikiIds(){
        $wikiIdsArray = array();
        $wikis = $this->getWikis();
        $wikisArray = json_decode($wikis);
        
        foreach ($wikisArray as $key => $value){
          //  echo $value->id;
            array_push($wikiIdsArray, $value->id);
        }
        return $wikiIdsArray;
    }
    
    /**
     * 
     * @return type JSON
     */
    public function getWikis(){
        $url_ = "/api/v2/wikis";

        $data = http_build_query(
                array(
                    "apiKey" => $this->apiKey,
                    "count" => 100,
                    "projectIdOrKey"=>$this->projectId
                )
                );
        $options = array("http" =>
                            array("method" => "GET",
                                        "header" => implode("\r\n", $this->header))
            );
        
        $contents = file_get_contents($this->url.$url_."?".$data,false,  stream_context_create($options));
        return $contents;
    }
  
    
    public function getAttachementFile($wikiId, $attachementId){
        $url_ = "/api/v2/wikis/".$wikiId."/attachments/".$attachementId;
                $data = http_build_query(
                array(
                    "apiKey" => $this->apiKey,
                    "projectIdOrKey"=>$this->projectId
                )
                );
        $options = array("http" =>
                            array("method" => "GET",
                                        "header" => implode("\r\n", $this->header))
            );
        $contents = file_get_contents($this->url.$url_."?".$data,false,  stream_context_create($options));
        return $contents;
    }
    
 }


