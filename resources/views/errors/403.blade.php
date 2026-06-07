@extends('errors.layout')
@section('code', '403')
@section('title', 'Access Forbidden')
@section('message', $exception->getMessage() ?: 'You don\'t have permission to access this page. If you believe this is a mistake, please contact your salon administrator.')
