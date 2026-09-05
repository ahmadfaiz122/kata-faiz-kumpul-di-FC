export const appState = $state({
    isProfileDropOpen: false,
    isNavbarMenuOpen: false
});

let activePage = "aa";

export function getActivePage(){
    return activePage;
}
    export function editPage(pageName){
    activePage = pageName;
    }

export function toggleProfileMenu(){
        appState.isProfileDropOpen = !appState.isProfileDropOpen;
        appState.isNavbarMenuOpen = false;
    };

export function toggleNavbarMenu(){
        appState.isNavbarMenuOpen = !appState.isNavbarMenuOpen;
        appState.isProfileDropOpen = false;
    };
export function closeProfileMenu(){
        appState.isProfileDropOpen = false;
    }

export function closeNavbarMenu(){
        appState.isNavbarMenuOpen = false;
    }