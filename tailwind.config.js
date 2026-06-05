/** @type {import('tailwindcss').Config} */
export default {
    content: ["./resources/**/*.blade.php", "./resources/**/*.js"],
    theme: {
        extend: {
            colors: {
                ink: "#0a0b0f",
                ink2: "#0e1016",
                surface: "#13151d",
                surface2: "#1a1d27",
                line: "#252934",
                line2: "#2f3441",
                ftext: "#eef0f6",
                fmuted: "#8b91a6",
                gold: "#c9a35a",
                gold2: "#e6c98b",
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', "system-ui", "sans-serif"],
                display: ["Fraunces", "Georgia", "serif"],
            },
        },
    },
    safelist: [
        // dynamic status classes built from PHP arrays — scanner can't see these
        "bg-fmuted/10",
        "text-[#a3a9bb]",
        "bg-[#6b9bf2]/10",
        "text-[#6b9bf2]",
        "bg-[#e0a94a]/10",
        "text-[#e0a94a]",
        "bg-[#46c08a]/10",
        "text-[#46c08a]",
        "bg-[#e87878]/10",
        "text-[#e87878]",
        "bg-gold/[.16]",
        "text-gold2",
    ],
    plugins: [],
};
