@extends('vendor.installer.layouts.master')
@section('template_title')
    Step 4 | Admin Login Info
@endsection

@section('title')
    <i class="fa fa-sign-in-alt fa-fw" aria-hidden="true"></i>
    Admin Login Info
@endsection

@section('container')
    <div class="tabs tabs-full">

        <form method="post" action="{{ route('LaravelInstaller::userSaveWizard') }}" class="">
            <div class="" id="">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group {{ $errors->has('first_name') ? ' has-error ' : '' }}">
                    <label for="first_name">
                        First Name
                    </label>
                    <input type="text" name="first_name" id="first_name" value="" placeholder="Type your first name" />
                    @if ($errors->has('first_name'))
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $errors->first('first_name') }}
                        </span>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('last_name') ? ' has-error ' : '' }}">
                    <label for="last_name">
                        Last Name
                    </label>
                    <input type="text" name="last_name" id="last_name" value="" placeholder="Type your last name" />
                    @if ($errors->has('first_name'))
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $errors->first('last_name') }}
                        </span>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('email') ? ' has-error ' : '' }}">
                    <label for="email">
                        Login Email
                    </label>
                    <input type="text" name="email" id="email" value="" placeholder="Type login email" />
                    @if ($errors->has('email'))
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $errors->first('email') }}
                        </span>
                    @endif
                </div>
                <div class="form-group {{ $errors->has('password') ? ' has-error ' : '' }}">
                    <label for="email">
                        Login Password
                    </label>
                    <input type="password" name="password" id="password" value="" placeholder="Type login password" />
                    @if ($errors->has('password'))
                        <span class="error-block">
                            <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                            {{ $errors->first('password') }}
                        </span>
                    @endif
                </div>

                <div class="buttons">
                    <input type="submit" value="Configure Environment" class="button">
                </div>
            </div>
        </form>

    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        function checkEnvironment(val) {
            var element=document.getElementById('environment_text_input');
            if(val=='other') {
                element.style.display='block';
            } else {
                element.style.display='none';
            }
        }
        function showDatabaseSettings() {
            document.getElementById('tab2').checked = true;
        }
        function showApplicationSettings() {
            document.getElementById('tab3').checked = true;
        }
    </script>
@endsection
