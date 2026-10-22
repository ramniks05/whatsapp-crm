@php
    $var = _i('This is a variable.');
    $n = 3;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ _i('This is the page title') }}</title>
</head>
<body>
    <p>{{ _i('There are %s red cars.', $n) }}</p>
    <p>{{ __('However, there are also blue cars.') }}</p>
    <p>{{ _n('There is also a single green car.', 'There are also %d green cars.', 2) }}</p>

    @section('test')
        @php
            /** Comment above the string */
            _i('Help, there is a comment above me.')
        @endphp
        @php
            _i(
                'This is a multi-line function call. %s',
                'Yes.'
            )
        @endphp
    @endsection
</body>
</html>