<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembershipPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_plans', function (Blueprint $table) {
			$table->engine = 'InnoDB';
            $table->id();
			$table->string('plan_name');
			$table->float('price_1')->default(0);
			$table->float('price_3')->default(0);
			$table->float('price_12')->default(0);
			$table->integer('trial_duration')->default(0);
			$table->float('monthly_discount_percentage')->default(0);
			$table->float('quarterly_discount_percentage')->default(0);
			$table->float('yearly_discount_percentage')->default(0);
			$table->string('discount_type')->nullable();
			$table->timestamp('discount_ends')->nullable();
			$table->tinyInteger('is_active')->default(0);
			$table->tinyInteger('is_featured')->default(0);
			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membership_plans');
    }
}
