<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        # заказы
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('org_code', 5);
            $table->enum('status', ['draft', 'pending', 'rejected', 'in_progress', 'completed', 'cancelled']);
            $table->text('comment')->nullable();
            $table->integer('requested_by');
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users');
            $table->foreign('org_code')->references('code')->on('organizations');
        });

        # история изменения статусов заказа
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->integer('id_order');
            $table->enum('status', ['draft', 'pending', 'rejected', 'in_progress', 'completed', 'cancelled']);
            $table->text('comment')->nullable();
            $table->integer('id_author');
            $table->timestamp('created_at');

            $table->foreign('id_order')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('id_author')->references('id')->on('users');
        });

        # заказы запчастей
        Schema::create('order_spare_part_details', function (Blueprint $table) {
            $table->id();
            $table->integer('id_order');
            $table->integer('id_printers_workplace');
            $table->integer('id_spare_part')->nullable();
            $table->boolean('call_specialist')->default(false);

            $table->foreign('id_order')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('id_printers_workplace')->references('id')->on('printers_workplace');
            $table->foreign('id_consumable')->references('id')->on('consumables');
        });

        # заказы запчастей - файлы
        Schema::create('order_spare_part_details_files', function (Blueprint $table) {
            $table->id();
            $table->integer('id_spare_part_order_detail');
            $table->string('filename', 500);
            $table->timestamp('created_at');

            $table->foreign('id_spare_part_order_detail')->references('id')->on('order_spare_part_details')->cascadeOnDelete();
        });



        # заказы картриджей
        Schema::create('order_consumables_details', function (Blueprint $table) {
            $table->id();
            $table->integer('id_order');
            $table->integer('id_consumable');
            $table->integer('quantity');
            $table->timestamps();
            $table->integer('id_author');

            $table->foreign('id_order')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('id_consumable')->references('id')->on('consumables');
        });



        # заказы мелочей
        Schema::create('order_misc_details', function (Blueprint $table) {
            $table->id();
            $table->integer('id_order');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->integer('id_author');

            $table->foreign('id_order')->references('id')->on('orders')->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_misc_details');
        Schema::dropIfExists('order_consumables_details');
        Schema::dropIfExists('order_spare_part_details_files');
        Schema::dropIfExists('order_spare_part_details');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('orders');
    }

};
