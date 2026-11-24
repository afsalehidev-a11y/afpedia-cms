<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ورود | AFPedia CMS</title></head>
<body>
<main>
    <h1>ورود به مدیریت AFPedia</h1>
    @if ($errors->any())
        <p role="alert">{{ $errors->first() }}</p>
    @endif
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <label for="email">ایمیل</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required>
        <label for="password">گذرواژه</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <label><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label>
        <button type="submit">ورود</button>
    </form>
</main>
</body>
</html>
