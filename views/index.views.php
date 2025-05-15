<?php require("views/partials/head.php"); ?>


<main class="min-h-screen flex justify-center items-center bg-slate-100 px-4">
  <div class="max-w-5xl grid grid-cols-1 md:grid-cols-2 w-full m-auto bg-white rounded-xl shadow-2xl overflow-hidden">

    <!-- Logo Section  -->
    <div class="flex justify-center items-center p-8 bg-gray-50">
      <img src="assets/images/logo.png" alt="Top rank logo" class="w-3/4 md:w-2/3 lg:w-1/2">
    </div>

    <!-- Login Form Section -->
    <div class="w-full p-8">
      <h2 class="font-bold text-3xl mb-6 text-slate-800">Login to Your Account</h2>
      <form action="/" class="space-y-5" method="POST">
        <div class="flex flex-col gap-3">
          <span class="login-error text-sm font-semibold text-pink-700 hidden"></span>
          <input type="text" placeholder="Username" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300 input-username" name="inputUsername">
          <input type="password" placeholder="Password" class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300 input-password" name="inputPassword">
        </div>
        <button class="w-full border-none bg-red-500 hover:bg-red-600 rounded text-white px-4 py-2 transition-colors font-semibold cursor-pointer btn-login" type="submit">Login</button>
      </form>
    </div>
  </div>

</main>



<script src="assets/js/index.js"></script>
<?php require("views/partials/footer.php"); ?>