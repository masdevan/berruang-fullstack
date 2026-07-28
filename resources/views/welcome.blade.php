<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite('resources/css/app.css')
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen items-center justify-center bg-[#090909] text-white">
            <h1 class="text-3xl font-bold">Blank</h1>
        </div>
    </body>
</html>
