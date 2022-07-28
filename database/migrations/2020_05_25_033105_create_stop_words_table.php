<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStopWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stop_words', function (Blueprint $table) {
            $table->id()->comment('敏感词 id');
            $table->string('ugc', 10)->comment('内容处理方式');
            $table->string('find')->comment('敏感词');
            $table->string('replacement')->nullable()->default('')->comment('替换词');
            $table->timestamps();

            $table->comment('敏感词表');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stop_words');
    }
}
