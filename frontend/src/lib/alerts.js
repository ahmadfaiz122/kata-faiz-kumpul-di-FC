import Swal from "sweetalert2";

const baseOptions = {
    buttonsStyling: false,
    customClass: {
        confirmButton: "swal-confirm",
        cancelButton: "swal-cancel",
    },
};

export function alertSuccess(title, text = "") {
    return Swal.fire({ ...baseOptions, icon: "success", title, text, confirmButtonText: "Oke" });
}

export function alertError(title, text = "") {
    return Swal.fire({ ...baseOptions, icon: "error", title, text, confirmButtonText: "Tutup" });
}

export function confirmAction(title, text) {
    return Swal.fire({
        ...baseOptions,
        icon: "warning",
        title,
        text,
        showCancelButton: true,
        confirmButtonText: "Ya, lanjutkan",
        cancelButtonText: "Batal",
        reverseButtons: true,
    });
}
