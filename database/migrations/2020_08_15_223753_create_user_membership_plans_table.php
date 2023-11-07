<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMembershipPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_membership_plans', function (Blueprint $table) {
			$table->engine = 'InnoDB';
            $table->id();
			$table->timestamps();
			$table->bigInteger('user_id')->default(0);
			$table->bigInteger('plan_id')->default(0);
			$table->tinyInteger('is_active')->default(1);
			$table->string('payment_method')->default('');
			$table->string('payment_id')->default('');
			$table->string('payment_gateway_ref')->nullable();
			$table->bigInteger('latest_invoice')->nullable();
			$table->timestamp('latest_invoice_date')->nullable();
			$table->tinyInteger('billing_interval')->default(1);
			$table->bigInteger('next_plan_id')->nullable();
			$table->timestamp('next_plan_start_from')->nullable();
			$table->bigInteger('previous_plan_id')->nullable();
			$table->tinyInteger('trial_active')->default(0);
			$table->tinyInteger('trial_used')->default(0);
			$table->timestamp('first_payment_date')->nullable();
			$table->float('first_payment_amount')->nullable();
			$table->timestamp('lastest_payment_date')->nullable();
			$table->float('lastest_payment_amount')->nullable();
			$table->string('payment_currency')->default('USD');
			$table->timestamp('plan_starts_at')->nullable();
			$table->timestamp('plan_ends_at')->nullable();
			$table->timestamp('trial_ends_at')->nullable();
			$table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_membership_plans');
    }
}
