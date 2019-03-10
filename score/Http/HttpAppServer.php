<?php
/**
+----------------------------------------------------------------------
| swoolefy framework bases on swoole extension development, we can use it easily!
+----------------------------------------------------------------------
| Licensed ( https://opensource.org/licenses/MIT )
+----------------------------------------------------------------------
| Author: bingcool <bingcoolhuang@gmail.com || 2437667702@qq.com>
+----------------------------------------------------------------------
*/

namespace Swoolefy\Http;

use Swoolefy\Core\Swfy;

abstract class HttpAppServer extends \Swoolefy\Http\HttpServer {

	/**
	 * __construct 初始化
	 * @param array $config
	 */
	public function __construct(array $config=[]) {
		// 获取当前服务文件配置
		$config = array_merge(
				include(__DIR__.'/config.php'),
				$config
			);
		parent::__construct($config);
	}

	/**
	 * onWorkerStart 
	 * @param   object  $server    
	 * @param   int     $worker_id 
	 * @return  void
	 */
	public abstract function onWorkerStart($server, $worker_id);

	/**
	 * onRequest 
	 * @param    $request
	 * @param    $response
	 * @return   void
	 */
	public function onRequest($request, $response) {
		if($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->end();
            return;
       	}
        self::$config['application_index']::getInstance($config = [])->run($request, $response);
	}

	/**
	 * onPipeMessage 
	 * @param    object  $server
	 * @param    int     $src_worker_id
	 * @param    mixed   $message
	 * @return   void
	 */
	public abstract function onPipeMessage($server, $from_worker_id, $message);

	/**
	 * onTask 异步任务处理
     * @param    mixed  $server
	 * @param    int  $task_id
	 * @param    int  $from_worker_id
	 * @param    mixed $data
	 * @return   void
	 */
	public function onTask($server, $task_id, $from_worker_id, $data) {
		list($callable, $extend_data, $fd) = $data;
		list($class, $action) = $callable;
		$taskInstance = new $class;
		$taskInstance->task_id = $task_id;
		$taskInstance->from_worker_id = $from_worker_id;
		$taskInstance->$action($extend_data);
		unset($callable, $taskInstance, $extend_data, $fd);
	}

	/**
	 * onFinish 
	 * @param    int   $task_id
	 * @param    mixed $data
	 * @return   void
	 */
	public function onFinish($server, $task_id, $data) {}

}	