<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPendingApprovalsTable extends Migration
{
    public function up()
    {
        Schema::table('pending_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('pending_approvals', 'deadline')) {
                $table->timestamp('deadline')->nullable()->after('comments');
            }

            if (!Schema::hasColumn('pending_approvals', 'priority')) {
                $table->string('priority')->default('medium')->after('deadline');
            }

            if (Schema::hasColumn('pending_approvals', 'requested_by') && !Schema::hasColumn('pending_approvals', 'requester_id')) {
                $table->renameColumn('requested_by', 'requester_id');
            }
        });
    }

    public function down()
    {
        Schema::table('pending_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('pending_approvals', 'deadline')) {
                $table->dropColumn('deadline');
            }

            if (Schema::hasColumn('pending_approvals', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('pending_approvals', 'requester_id')) {
                $table->renameColumn('requester_id', 'requested_by');
            }
        });
    }
}
