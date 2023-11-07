<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments_log', function (Blueprint $table) {
			$table->engine = 'InnoDB';
            $table->id();
			$table->timestamps();
            $table->bigInteger('user_id')->default(0);
			$table->bigInteger('plan_id')->default(0);
			$table->tinyInteger('billing_interval')->default(1);
			$table->string('txn_id')->nullable();
			$table->string('ref_id')->nullable();
			$table->string('request_origin')->default('webhook');
			$table->float('amount')->default(0);
			$table->string('currency')->default('USD');
			$table->string('coupon_code')->nullable();
			$table->tinyInteger('has_trial')->default(0);
			$table->string('payment_method')->nullable();
			$table->string('payment_id')->nullable();
			$table->timestamp('payment_period_start')->nullable();
			$table->timestamp('payment_period_end')->nullable();
			$table->string('txn_status')->nullable();
			$table->string('txn_status_custom')->nullable();
			$table->text('reason')->nullable();
			$table->longText('raw_data')->nullable();
			$table->text('txn_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments_log');
    }
}
