<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar'
import { Link, usePage } from '@inertiajs/vue3'
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuShortcut,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { Separator } from '@/Components/ui/separator'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuAction,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarProvider,
    SidebarRail,
    SidebarTrigger,
} from '@/Components/ui/sidebar'
import {
    AudioWaveform,
    BadgeCheck,
    Bell,
    BookOpen,
    Bot,
    ChevronRight,
    ChevronsUpDown,
    CircleUser,
    Command,
    CreditCard,
    Folder,
    Forward,
    Frame,
    GalleryVerticalEnd,
    LogOut,
    Map,
    MoreHorizontal,
    PieChart,
    Plus,
    Search,
    Settings2,
    Sparkles,
    SquareTerminal,
    Trash2,
    LayoutDashboard,
    Wallet,
    Tags,
    Receipt,
    Target,
    User,
} from 'lucide-vue-next'
import { computed, ref } from 'vue'
import DarkModeDropdown from '@/Components/ui/dark-mode-dropdown/DarkModeDropdown.vue'
import Toaster from '@/Components/ui/toast/Toaster.vue'

type AuthUser = { name: string; email: string; avatar?: string | null }

const page = usePage()
const user = computed(() => page.props.auth?.user as AuthUser | null)

// Sidebar principal de Spendo
const navMain = computed(() => [
    {
        title: 'Panel',
        url: route('dashboard'),
        icon: LayoutDashboard,
        isActive: route().current('dashboard'),
        items: [],
    },
    {
        title: 'Transacciones',
        url: route('transactions.index'),
        icon: Receipt,
        isActive: route().current('transactions.*'),
        items: [],
    },
    {
        title: 'Cuentas',
        url: route('accounts.index'),
        icon: Wallet,
        isActive: route().current('accounts.*'),
        items: [],
    },
    {
        title: 'Categorías',
        url: route('categories.index'),
        icon: Tags,
        isActive: route().current('categories.*'),
        items: [],
    },
    {
        title: 'Presupuestos',
        url: route('budgets.index'),
        icon: Target,
        isActive: route().current('budgets.*'),
        items: [],
    },
])

// Equipos/proyectos siguen siendo de ejemplo visual
const data = {
    teams: [
        { name: 'Spendo', logo: GalleryVerticalEnd, plan: 'Personal' },
    ],
}

const activeTeam = ref(data.teams[0])

function setActiveTeam(team: (typeof data.teams)[number]) {
    activeTeam.value = team
}

const initials = computed(() => {
    const name = user.value?.name ?? 'Usuario'
    const parts = name.trim().split(/\s+/).filter(Boolean)
    const first = parts[0]?.[0] ?? 'U'
    const second = parts[1]?.[0] ?? ''
    return (first + second).toUpperCase()
})
</script>

<template>
    <Toaster />
    <SidebarProvider>
        <Sidebar collapsible="icon">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton
                                    size="lg"
                                    class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                >
                                    <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                                        <component :is="activeTeam.logo" class="size-4" />
                                    </div>
                                    <div class="grid flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ activeTeam.name }}</span>
                                        <span class="truncate text-xs">{{ activeTeam.plan }}</span>
                                    </div>
                                    <ChevronsUpDown class="ml-auto" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                                align="start"
                                side="bottom"
                                :side-offset="4"
                            >
                                <DropdownMenuLabel class="text-xs text-muted-foreground">
                                    Equipos
                                </DropdownMenuLabel>
                                <DropdownMenuItem
                                    v-for="(team, index) in data.teams"
                                    :key="team.name"
                                    class="gap-2 p-2"
                                    @click="setActiveTeam(team)"
                                >
                                    <div class="flex size-6 items-center justify-center rounded-sm border">
                                        <component :is="team.logo" class="size-4 shrink-0" />
                                    </div>
                                    {{ team.name }}
                                    <DropdownMenuShortcut>⌘{{ index + 1 }}</DropdownMenuShortcut>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup>
                    <SidebarGroupLabel>Spendo</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in navMain"
                            :key="item.title"
                        >
                            <SidebarMenuButton as-child :class="item.isActive ? 'bg-sidebar-accent text-sidebar-accent-foreground' : ''">
                                <Link :href="item.url">
                                    <component :is="item.icon" />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>

                <!-- Projects demo (opcional, se puede quitar si no lo usas) -->
                <SidebarGroup class="group-data-[collapsible=icon]:hidden">
                    <SidebarGroupLabel>Projects</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in data.projects"
                            :key="item.name"
                        >
                            <SidebarMenuButton as-child>
                                <a :href="item.url">
                                    <component :is="item.icon" />
                                    <span>{{ item.name }}</span>
                                </a>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <SidebarMenuButton
                                    size="lg"
                                    class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                                >
                                    <Avatar class="h-8 w-8 rounded-lg">
                                        <AvatarImage
                                            v-if="user?.avatar"
                                            :src="user?.avatar as string"
                                            :alt="user?.name ?? 'User'"
                                        />
                                        <AvatarFallback class="rounded-lg">
                                            {{ initials }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="grid flex-1 text-left text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ user?.name ?? 'Usuario' }}</span>
                                        <span class="truncate text-xs">{{ user?.email ?? '' }}</span>
                                    </div>
                                    <ChevronsUpDown class="ml-auto size-4" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                                side="bottom"
                                align="end"
                                :side-offset="4"
                            >
                                <DropdownMenuLabel class="p-0 font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                        <Avatar class="h-8 w-8 rounded-lg">
                                            <AvatarImage
                                                v-if="user?.avatar"
                                                :src="user?.avatar as string"
                                                :alt="user?.name ?? 'User'"
                                            />
                                            <AvatarFallback class="rounded-lg">
                                                {{ initials }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="grid flex-1 text-left text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ user?.name ?? 'Usuario' }}</span>
                                            <span class="truncate text-xs">{{ user?.email ?? '' }}</span>
                                        </div>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuGroup>
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('profile.edit')" class="flex items-center gap-2">
                                            <User class="size-4" />
                                            Perfil
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuGroup>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center gap-2"
                                    >
                                        <LogOut class="size-4" />
                                        Cerrar sesión
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>

        <SidebarInset>
            <header
                class="flex h-16 shrink-0 items-center mr-6 gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12"
            >
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 h-4" />
                    <Breadcrumb>
                        <BreadcrumbList>
                            <BreadcrumbItem class="hidden md:block">
                                <BreadcrumbLink href="#">
                                    Spendo
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator class="hidden md:block" />
                            <BreadcrumbItem>
                                <BreadcrumbPage>
                                    <!-- Slot opcional para un título más específico -->
                                    <slot name="breadcrumb">Panel</slot>
                                </BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>
                </div>
                <div class="relative ml-auto flex-1 md:grow-0">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <input
                        type="search"
                        placeholder="Buscar..."
                        class="w-full rounded-lg bg-background pl-8 md:w-[200px] lg:w-[336px] border border-input px-2 py-1 text-sm"
                    />
                </div>

                <DarkModeDropdown />
            </header>

            <div v-if="$slots.header" class="px-4 py-4">
                <slot name="header" />
            </div>

            <main class="px-4 pb-10">
                <slot />
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>

