@extends('vendor.installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.welcome.templateTitle') }}
@endsection

@section('title')
    {{ trans('installer_messages.welcome.title') }}
@endsection

@section('container')
    <p class="text-center welcome-message">
        {{ trans('installer_messages.ready_for_install') }}
    </p>
    <p class="text-center">
        <a href="{{ route('LaravelInstaller::database') }}" class="button">
            {{ trans('installer_messages.install_now') }}
            <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
        </a>
    </p>
@endsection
