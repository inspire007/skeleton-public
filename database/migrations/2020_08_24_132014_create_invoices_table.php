<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
			$table->engine = 'InnoDB';
            $table->id();
            $table->timestamps();
			$table->bigInteger('user_id')->default(0);
			$table->bigInteger('plan_id')->default(0);
			$table->integer('billing_interval')->default(1);
			$table->bigInteger('payments_log_id')->default(0);
			$table->string('payment_ref')->nullable();
			$table->string('txn_id')->nullable();
			$table->float('membership_balance_used')->default(0);
			$table->integer('membership_balance_days')->default(0);
			$table->float('billing_price')->default(0);
			$table->float('amount_due')->default(0);
			$table->float('amount_paid')->default(0);
			$table->integer('trial_days')->default(0);
			$table->string('currency')->default('USD');
			$table->string('coupon_code')->nullable();
			$table->float('discounted_amount')->default(0);
			$table->timestamp('payment_due_date')->nullable();
			$table->timestamp('payment_made_date')->nullable();
			$table->timestamp('next_payment_date')->nullable();
			$table->timestamp('invoice_period_start')->nullable();
			$table->timestamp('invoice_period_end')->nullable();
			$table->string('invoice_status')->nullable();
			$table->string('invoice_action_require')->nullable();
			$table->text('invoice_comments')->nullable();
			$table->string('invoice_origin')->nullable();
			$table->tinyInteger('invoice_mailed')->default(0);
			$table->timestamp('next_reminder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
