<?php
App::uses('AppModel', 'Model');    //仮pluginにいこうするまで
App::uses('Validation', 'Utility');    //仮pluginにいこうするまで
//App::uses('InformationAppModel', 'Information.Model');
/**
 * Information Model
 *
 * @property User $User
 */

//class Information extends InformationAppModel {
 class Information extends AppModel {    //仮pluginにいこうするまで

 	var $name = 'Information';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';




/*
 * このorder by 句は、お知らせ一覧画面で使用
 *
 */
	var $order = array( 'Information.id'	=>	'desc' );

//なくても読み込むのでいらない↓
//	var $actsAs = array( 'Multivalidatable',//複数のバリデーションルールを使用する
//	                      'Sanitize',       //サニタイズを使用する
//						  'Common');

/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 *
 */
	var $sanitizeItems = array(	'name' => array('html' => true, ),	//infoのNAME項目って何？
								'title_jpn' => array('html'=>true),
								'title_eng' => array('html'=>true),
								'data_jpn' => array('html'=> true),
								'data_eng' => array('html'=> true),
								);



/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

	/*
	 * その他のバリデーションルール
	 */
	var $validationSets =
		array(
			'default' => array(
				'id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'last' => true,
						'message' => array('Invalid %s .','ID'),
					),
				),
			),
			'update' => array(
				'name' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'required' => true,
						'last' => false,
						'message' => 'The blank is not goodness.'
					),
				),
				'putdate'=> array(
    				'date' => array(
    					'rule' => array('date'),
    					'allowEmpty' => true,
    					'last' => true,
    					'message' => 'Please enter in the valid date format.',
    				),
					'infoDate' => array(
						'rule' => array('infoDate'),
						'required' => false,
						'last' => true,
						'message' => 'Please specify putdate or outdate.'
					),
					'infoFromTo' => array(
						'rule' => array('infoFromTo'),
						'required' => false,
						'last' => true,
						'message' => 'Please set the outdate after an putdate.'
					),
				),
    			'outdate' => array(
    				'date' => array(
    					'rule' => array('date'),
    					'allowEmpty' => true,
    					'last' => true,
    					'message' => 'Please enter in the valid date format.',
    				),
    			),
		),





// -----------------------------------------------
			//データ識別が「有効」の時のチェック内容
/*
			'valid_insert'	=> array(
	          'title'	=>	array(
							      'stripTag'	=>	array(
										'rule'	=>	'stripTag',
										'last'	=>	true,
									'message' => 'The Title is wrong'),
									),

			 					'is_put'=>	array(
										'rule'	=> array('equalTo', '1'),
										'message'=> 'The Kind of data is wrong'
																),
								'putdate'=> array(
											'notBlank'	=>	array(
													'rule'	=>	array('notBlank'),
													'last'	=> true,
													'message'=>	'The Start date is not empty' ),
											'date'		=>	array(
													'rule'=> 'date',
													'last'=> true,
													'message'=> 'The Start date is wrong' )
																),
											'importance'	=> array(
													'rule'	=>	array('inList', array('001', '002', '003') ),
												 'message'	=> 'The Importance is wrong'
																),
									)
			//データ識別が「無効」の時のチェック内容
			,'invalid_insert'	=> array(
								'is_put'=> array(
										'rule'	=> array('equalTo', '0'),
										'message'	=> 'The Kind of data is wrong'
																),
								'importance'=> array(
										'rule'	=>	array(	'inList', array('001', '002', '003')	),
								 	'message'	=> 'The Importance is wrong'
								 								),
									)
*/
		);


/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	public function beforeValidate() {
		// Titleデフォルト値設定
		if(Hash::check($this->data,'Information.title_jpn')){
			if ($this->data[$this->name]['title_jpn'] == '') {
				$this->data[$this->name]['title_jpn'] = $this->data[$this->name]['name'];
			}
		}
		if(Hash::check($this->data,'Information.title_eng')){
			if ($this->data[$this->name]['title_eng'] == '') {
				$this->data[$this->name]['title_eng'] = $this->data[$this->name]['name'];
			}
		}
	}

	public function afterValidate($options) {
		if (!$this->validationErrors) {
			// バリデーションOK
			if (empty($this->data[$this->name]['putdate'])) {
				// 開始日がない場合は今日の日付け入力
				$this->data[$this->name]['putdate'] = date('Y-m-d 00:00:00');
			}
		}
	}

	function save($data) {
		try{
$this->log('save start.');
//$this->log($data);
			return(parent::save($data));
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
* func info_notTitleEmpty
* @brief お知らせタイトルが最低ひとつは入っていることをチェック
* @param mix title_jpn :
* @param mix title_eng :
* @retval boolean $ret :
*/
    function info_notTitleEmpty($data){
		try{
			if(	empty($this->data[$this->name]['title_jpn']) &&
				empty($this->data[$this->name]['title_eng']))
			{
				return false;
			}
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}

/**
* func infoDate
* @brief 日付指定チェック outdate のみの記載はエラー
* @param mix putdate :
* @param mix outdate :
* @retval boolean $ret :
*/
    function infoDate($data){
		try{
			if(	empty($this->data[$this->name]['putdate'])){
				// from 指定なし
				if(empty($this->data[$this->name]['outdate'])){
					// from to とも　指定なし - OK
					return false;
				}
			}
			// 開始日か終了日の指定があればとりあえずOK
			return true;

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}


/**
* func infoFromTo
* @brief お知らせタイトルが最低ひとつは入っていることをチェック
* @param mix putdate :
* @param mix outdate :
* @retval boolean $ret :
*/
    function infoFromTo($data){
		try{
			if(	empty($this->data[$this->name]['putdate']) ||
				empty($this->data[$this->name]['outdate']))
			{
				// 両方とも指定なし
				return true;
			}

//			if(	(Validation::date($this->data[$this->name]['putdate'],'ymd')) &&
//				(Validation::date($this->data[$this->name]['outdate'],'ymd'))) {
				$chk = $this->cmpdate($this->data[$this->name]['outdate'],
										$this->data[$this->name]['putdate']);
				if($chk > 0){
					// 期間OK
					return true;
				}
//			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}

/**
* display
* @brief お知らせタイトルが最低ひとつは入っていることをチェック
* @param mix putdate :
* @param mix outdate :
* @retval boolean $ret :
*/
    function display($id = null){
		try{
			$this->recursive = -1;
			if($id === null){
				$this->setFlash(__('Invalid %s .','ID'),'Flash/error');
				return false;
			}
			$data = $this->findById($id);

			$today = $this->today();
			$putdate = $data[$this->name]['putdate'];
			$outdate = $data[$this->name]['outdate'];

			$rc = $this->cmpdate($putdate,$today);
			if($rc >= 0){
				// 期限前だったらスタート日付を前倒し
				$putdate = $today;
			}
			if(empty($outdate)){
			} else {
			$rc = $this->cmpdate($outdate,$today);
				if($rc < 0){
					// 終わっていたらとりあえず１週間延長
					$outdate = $this->getexpday(7);
				}
			}

			$this->setValidation('default');

			$update = array();
			$update['id'] = $id;
			$update['is_put'] = 1;
			$update['putdate'] = $putdate;
			$update['outdate'] = $outdate;
			$update['modified'] = null;

			return(parent::save($update));

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}

/**
* hide
* @brief お知らせタイトルが最低ひとつは入っていることをチェック
* 			日付関係はチェックせず、フラグのみ「無効」とする
* @retval boolean $ret :
*/
    function hide($id = null){
		try{
			$this->recursive = -1;
			if($id === null){
				$this->setFlash(__('Invalid %s .','ID'),'Flash/error');
				return false;
			}
			$data = $this->findById($id);

			$this->setValidation('default');

			$update = array();
			$update['id'] = $id;
			$update['is_put'] = 0;
			$update['modified'] = null;
			return(parent::save($update));

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}

/**
* getInfo
* @brief 表示用お知らせ取り出し
* @param int max : 持ってくる最大数
* @retval boolean $ret :
*/
	function getInfo($max = 3){
		try{
			$limit = $max;
			$today = $this->today();
			$or_cond = array('or' => array(
							$this->name.'.putdate' => null ,
							array($this->name.'.putdate <=' => $today,
								  $this->name.'.outdate' => null),
							array($this->name.'.putdate <=' => $today,
								  $this->name.'.outdate >' => $today)));
			$cond = array($this->name.'.is_put' => 1,
							$or_cond);

			$options = array(	'conditions' => $cond,
								'recursive' => -1,
								'order' => array(
												$this->name.'.importance desc',
												$this->name.'.putdate desc',
											),
								'limit' => $limit);

			$data = $this->find('all',$options);
            foreach($data as $k => $v){
                // 英語が入っていなかったら日本語を入れておく
                if(empty($v['Information']['data_jpn']) && !empty($v['Information']['data_eng'])){
                    $data[$k]['Information']['data_jpn'] = $data[$k]['Information']['data_eng'];
                } elseif(empty($v['Information']['data_eng']) && !empty($v['Information']['data_jpn'])){
                    // 日本語が入っていなかったら英語を入れておく
                    $data[$k]['Information']['data_eng'] = $data[$k]['Information']['data_jpn'];
                }
            }
			return ($data);
		} catch (Exception $e){
debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;

	}
/* ---------------------------------------------
 * find後処理
 * ---------------------------------------------
 */

	function afterFind($results) {
        $rs = parent::afterFind($results);
		foreach ($rs as $idx => $rec) {
			if(is_numeric($idx) and !empty($rec['Information'])){

				foreach($rec['Information'] as $colName => $val){
					switch( $colName ) {
						case 'title_jpn':
						case 'title_eng':
							if(empty($val)){
								$rs[$idx]['Information'][$colName] =$rs[$idx]['Information']['name'];
							}
							break;
						case 'putdate':
						case 'outdate':

							//日付型の値を YYYY-MM-DD の形にする
							if((empty($val))||(!isset($val))||($val=='')||($val=='0000-00-00 00:00:00') ) {

							 //表示終了日に入力がなかった場合は、NULLをセット
							 //     $results[$idx]['Information']['outdate'] = NULL;
									$rs[$idx]['Information'][$colName] = NULL;

							}else{

								$rs[$idx]['Information'][$colName] = $this->dateFormatAfterFind($val);
							}
							break;

					}
				}
			}
		}
		return $rs;
	}

/* =============================================
 * 日付型の値を YYYY-MM-DD の形にする
 * =============================================
*/
	function dateFormatAfterFind($dateString) {
		return date('Y-m-d', strtotime($dateString));
//debug('st');
	}
}
?>
