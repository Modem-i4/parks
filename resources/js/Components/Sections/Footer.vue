<script setup>
import { Link } from '@inertiajs/vue3'

const year = new Date().getFullYear()
</script>

<template>
  <footer class="bg-[#007C57] text-white">
    <div class="mx-auto w-full max-w-[1500px] px-4 sm:px-6 lg:px-8 py-8 md:py-10 lg:py-12">

      <div class="grid grid-cols-2 md:grid-cols-[1fr_auto_1fr] items-center gap-4 md:gap-8">
        <Link href="/parks" class="justify-self-start inline-flex items-center gap-3 md:gap-4">
          <svg viewBox="0 0 48 48" class="w-10 h-10 md:w-12 md:h-12 shrink-0" fill="none" stroke="currentColor" stroke-width="3">
            <path d="M17 10 31 4v34l-14 6V10Z" />
            <path d="M31 4l13 6v28l-13-6M17 10 4 4v28l13 6" />
          </svg>
          <span class="font-semibold leading-none text-2xl md:text-3xl">Мапа парків</span>
        </Link>

        <div class="col-span-2 md:col-span-1 order-3 md:order-none text-center">
          <div class="text-base md:text-lg font-medium">
            © {{ year }} КП «Центр розвитку міста та рекреації»
          </div>
        </div>

        <Link href="/news" class="justify-self-end inline-flex items-center gap-3 md:gap-4 order-2 md:order-none">
          <svg viewBox="0 0 48 48" class="w-10 h-10 md:w-12 md:h-12 shrink-0" fill="none" stroke="currentColor" stroke-width="3">
            <rect x="6" y="6" width="28" height="36" rx="3" />
            <path d="M36 12h6v24a6 6 0 0 1-6 6H12" />
            <path d="M12 14h16M12 20h16M12 26h12M12 32h10" />
          </svg>
          <span class="font-semibold leading-none text-2xl md:text-3xl">Новини</span>
        </Link>
      </div>

      <div class="my-6 md:my-8 border-t border-white/50"></div>

      <div class="grid grid-cols-1 lg:grid-cols-12 items-start gap-8 md:gap-10">
        <p class="lg:col-span-7 text-white/95 text-lg my-auto">
          Цей сайт розроблений в межах реалізації Проєкту «Міські парки мають значення – новий підхід до управління міськими зеленими насадженнями в Івано-Франківську та Жешуві» в рамках Програми «Interreg NEXT Польща-Україна 2021-2027».
        </p>
        <div class="lg:col-span-5">
          <div class="flex flex-col sm:flex-row lg:items-end justify-center lg:justify-end gap-8 md:gap-10">
            <a href="https://pl-ua.eu/en/" target="_blank"
              class="flex-1 min-w-0 max-w-full h-auto max-h-22 md:max-h-24 lg:max-h-28 mx-auto"
            >
              <img src="/img/icons/logo-project-hor.jpg" alt="ParksMatter" class="rounded" />
            </a>
          </div>
          <div class="mt-6 md:mt-8 flex items-center gap-2 justify-center lg:justify-end text-sm md:text-base text-white/80">
            <span>Розробка</span>
            <a href="http://everydev.great-site.net/" target="_blank" rel="noopener" class="inline-flex items-center gap-2 hover:opacity-90">
              <img src="/img/icons/logo-everydev.png" alt="EveryDev" class="h-5 w-auto object-contain" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
