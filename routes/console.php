<?php
use Illuminate\Support\Facades\Schedule;

// Revisar SLA cada hora
Schedule::command('helpdesk:recordar-sla')->hourly();
