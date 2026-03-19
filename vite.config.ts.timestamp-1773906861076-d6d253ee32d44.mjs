// vite.config.ts
import { defineConfig } from "file:///C:/Users/amit1/Data/AppData/App/ganesh/Personal/github/the-patil-photography-codeignetor/node_modules/vite/dist/node/index.js";
import react from "file:///C:/Users/amit1/Data/AppData/App/ganesh/Personal/github/the-patil-photography-codeignetor/node_modules/@vitejs/plugin-react-swc/index.js";
import path from "path";
var __vite_injected_original_dirname = "C:\\Users\\amit1\\Data\\AppData\\App\\ganesh\\Personal\\github\\the-patil-photography-codeignetor";
var vite_config_default = defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 8080,
    fs: {
      allow: [
        path.resolve(__vite_injected_original_dirname, "."),
        path.resolve(__vite_injected_original_dirname, "./client"),
        path.resolve(__vite_injected_original_dirname, "./shared"),
        path.resolve(__vite_injected_original_dirname, "./website"),
        path.resolve(__vite_injected_original_dirname, "./node_modules")
      ],
      deny: [".env", ".env.*", "*.{crt,pem}", "**/.git/**", "server/**", "website/node_modules_backup/**"]
    }
  },
  build: {
    outDir: "dist/spa",
    rollupOptions: {
      input: {
        main: path.resolve(__vite_injected_original_dirname, "index.html"),
        admin: path.resolve(__vite_injected_original_dirname, "admin/index.html")
      }
    }
  },
  plugins: [react(), expressPlugin()],
  resolve: {
    dedupe: ["react", "react-dom"],
    alias: {
      "@": path.resolve(__vite_injected_original_dirname, "./client"),
      "@shared": path.resolve(__vite_injected_original_dirname, "./shared"),
      "@website": path.resolve(__vite_injected_original_dirname, "./website")
    }
  }
}));
console.log("Vite config loaded - triggering restart " + Date.now());
function expressPlugin() {
  return {
    name: "express-plugin",
    apply: "serve",
    // Only apply during development (serve mode)
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        const url = req.url || "";
        if (url.startsWith("/admin") && !url.startsWith("/api") && !url.includes(".")) {
          req.url = "/admin/index.html";
        }
        next();
      });
    }
  };
}
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcudHMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJDOlxcXFxVc2Vyc1xcXFxhbWl0MVxcXFxEYXRhXFxcXEFwcERhdGFcXFxcQXBwXFxcXGdhbmVzaFxcXFxQZXJzb25hbFxcXFxnaXRodWJcXFxcdGhlLXBhdGlsLXBob3RvZ3JhcGh5LWNvZGVpZ25ldG9yXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJDOlxcXFxVc2Vyc1xcXFxhbWl0MVxcXFxEYXRhXFxcXEFwcERhdGFcXFxcQXBwXFxcXGdhbmVzaFxcXFxQZXJzb25hbFxcXFxnaXRodWJcXFxcdGhlLXBhdGlsLXBob3RvZ3JhcGh5LWNvZGVpZ25ldG9yXFxcXHZpdGUuY29uZmlnLnRzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9DOi9Vc2Vycy9hbWl0MS9EYXRhL0FwcERhdGEvQXBwL2dhbmVzaC9QZXJzb25hbC9naXRodWIvdGhlLXBhdGlsLXBob3RvZ3JhcGh5LWNvZGVpZ25ldG9yL3ZpdGUuY29uZmlnLnRzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnLCBQbHVnaW4gfSBmcm9tIFwidml0ZVwiO1xyXG5pbXBvcnQgcmVhY3QgZnJvbSBcIkB2aXRlanMvcGx1Z2luLXJlYWN0LXN3Y1wiO1xyXG5pbXBvcnQgcGF0aCBmcm9tIFwicGF0aFwiO1xyXG4vLyBJbXBvcnQgdGhlIGV4cGxpY2l0IHNlcnZlciBlbnRyeSBzbyBWaXRlIHJlc29sdmVzIHRoZSBjb3JyZWN0IG1vZHVsZVxyXG4vLyBpbXBvcnQgeyBjcmVhdGVTZXJ2ZXIgfSBmcm9tIFwiLi9zZXJ2ZXIvaW5kZXguanNcIjtcclxuXHJcbi8vIGh0dHBzOi8vdml0ZWpzLmRldi9jb25maWcvXHJcbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZygoeyBtb2RlIH0pID0+ICh7XHJcbiAgc2VydmVyOiB7XHJcbiAgICBob3N0OiBcIjo6XCIsXHJcbiAgICBwb3J0OiA4MDgwLFxyXG4gICAgZnM6IHtcclxuICAgICAgYWxsb3c6IFtcclxuICAgICAgICBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCBcIi5cIiksXHJcbiAgICAgICAgcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgXCIuL2NsaWVudFwiKSxcclxuICAgICAgICBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCBcIi4vc2hhcmVkXCIpLFxyXG4gICAgICAgIHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsIFwiLi93ZWJzaXRlXCIpLFxyXG4gICAgICAgIHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsIFwiLi9ub2RlX21vZHVsZXNcIiksXHJcbiAgICAgIF0sXHJcbiAgICAgIGRlbnk6IFtcIi5lbnZcIiwgXCIuZW52LipcIiwgXCIqLntjcnQscGVtfVwiLCBcIioqLy5naXQvKipcIiwgXCJzZXJ2ZXIvKipcIiwgXCJ3ZWJzaXRlL25vZGVfbW9kdWxlc19iYWNrdXAvKipcIl0sXHJcbiAgICB9LFxyXG4gIH0sXHJcbiAgYnVpbGQ6IHtcclxuICAgIG91dERpcjogXCJkaXN0L3NwYVwiLFxyXG4gICAgcm9sbHVwT3B0aW9uczoge1xyXG4gICAgICBpbnB1dDoge1xyXG4gICAgICAgIG1haW46IHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsIFwiaW5kZXguaHRtbFwiKSxcclxuICAgICAgICBhZG1pbjogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgXCJhZG1pbi9pbmRleC5odG1sXCIpLFxyXG4gICAgICB9LFxyXG4gICAgfSxcclxuICB9LFxyXG4gIHBsdWdpbnM6IFtyZWFjdCgpLCBleHByZXNzUGx1Z2luKCldLFxyXG4gIHJlc29sdmU6IHtcclxuICAgIGRlZHVwZTogW1wicmVhY3RcIiwgXCJyZWFjdC1kb21cIl0sXHJcbiAgICBhbGlhczoge1xyXG4gICAgICBcIkBcIjogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgXCIuL2NsaWVudFwiKSxcclxuICAgICAgXCJAc2hhcmVkXCI6IHBhdGgucmVzb2x2ZShfX2Rpcm5hbWUsIFwiLi9zaGFyZWRcIiksXHJcbiAgICAgIFwiQHdlYnNpdGVcIjogcGF0aC5yZXNvbHZlKF9fZGlybmFtZSwgXCIuL3dlYnNpdGVcIiksXHJcbiAgICB9LFxyXG4gIH0sXHJcbn0pKTtcclxuXHJcbmNvbnNvbGUubG9nKCdWaXRlIGNvbmZpZyBsb2FkZWQgLSB0cmlnZ2VyaW5nIHJlc3RhcnQgJyArIERhdGUubm93KCkpO1xyXG5mdW5jdGlvbiBleHByZXNzUGx1Z2luKCk6IFBsdWdpbiB7XHJcbiAgcmV0dXJuIHtcclxuICAgIG5hbWU6IFwiZXhwcmVzcy1wbHVnaW5cIixcclxuICAgIGFwcGx5OiBcInNlcnZlXCIsIC8vIE9ubHkgYXBwbHkgZHVyaW5nIGRldmVsb3BtZW50IChzZXJ2ZSBtb2RlKVxyXG4gICAgY29uZmlndXJlU2VydmVyKHNlcnZlcikge1xyXG4gICAgICAvLyBTUEEgRmFsbGJhY2sgZm9yIC9hZG1pblxyXG4gICAgICBzZXJ2ZXIubWlkZGxld2FyZXMudXNlKChyZXEsIHJlcywgbmV4dCkgPT4ge1xyXG4gICAgICAgIGNvbnN0IHVybCA9IHJlcS51cmwgfHwgXCJcIjtcclxuICAgICAgICBpZiAodXJsLnN0YXJ0c1dpdGgoXCIvYWRtaW5cIikgJiYgIXVybC5zdGFydHNXaXRoKFwiL2FwaVwiKSAmJiAhdXJsLmluY2x1ZGVzKFwiLlwiKSkge1xyXG4gICAgICAgICAgcmVxLnVybCA9IFwiL2FkbWluL2luZGV4Lmh0bWxcIjtcclxuICAgICAgICB9XHJcbiAgICAgICAgbmV4dCgpO1xyXG4gICAgICB9KTtcclxuXHJcbiAgICAgIC8vIGNvbnN0IGFwcCA9IGNyZWF0ZVNlcnZlcih7IG1pZGRsZXdhcmVNb2RlOiB0cnVlIH0pO1xyXG5cclxuICAgICAgLy8gQWRkIEV4cHJlc3MgYXBwIGFzIG1pZGRsZXdhcmUgdG8gVml0ZSBkZXYgc2VydmVyXHJcbiAgICAgIC8vIHNlcnZlci5taWRkbGV3YXJlcy51c2UoYXBwKTtcclxuICAgIH0sXHJcbiAgfTtcclxufVxyXG4iXSwKICAibWFwcGluZ3MiOiAiO0FBQThjLFNBQVMsb0JBQTRCO0FBQ25mLE9BQU8sV0FBVztBQUNsQixPQUFPLFVBQVU7QUFGakIsSUFBTSxtQ0FBbUM7QUFPekMsSUFBTyxzQkFBUSxhQUFhLENBQUMsRUFBRSxLQUFLLE9BQU87QUFBQSxFQUN6QyxRQUFRO0FBQUEsSUFDTixNQUFNO0FBQUEsSUFDTixNQUFNO0FBQUEsSUFDTixJQUFJO0FBQUEsTUFDRixPQUFPO0FBQUEsUUFDTCxLQUFLLFFBQVEsa0NBQVcsR0FBRztBQUFBLFFBQzNCLEtBQUssUUFBUSxrQ0FBVyxVQUFVO0FBQUEsUUFDbEMsS0FBSyxRQUFRLGtDQUFXLFVBQVU7QUFBQSxRQUNsQyxLQUFLLFFBQVEsa0NBQVcsV0FBVztBQUFBLFFBQ25DLEtBQUssUUFBUSxrQ0FBVyxnQkFBZ0I7QUFBQSxNQUMxQztBQUFBLE1BQ0EsTUFBTSxDQUFDLFFBQVEsVUFBVSxlQUFlLGNBQWMsYUFBYSxnQ0FBZ0M7QUFBQSxJQUNyRztBQUFBLEVBQ0Y7QUFBQSxFQUNBLE9BQU87QUFBQSxJQUNMLFFBQVE7QUFBQSxJQUNSLGVBQWU7QUFBQSxNQUNiLE9BQU87QUFBQSxRQUNMLE1BQU0sS0FBSyxRQUFRLGtDQUFXLFlBQVk7QUFBQSxRQUMxQyxPQUFPLEtBQUssUUFBUSxrQ0FBVyxrQkFBa0I7QUFBQSxNQUNuRDtBQUFBLElBQ0Y7QUFBQSxFQUNGO0FBQUEsRUFDQSxTQUFTLENBQUMsTUFBTSxHQUFHLGNBQWMsQ0FBQztBQUFBLEVBQ2xDLFNBQVM7QUFBQSxJQUNQLFFBQVEsQ0FBQyxTQUFTLFdBQVc7QUFBQSxJQUM3QixPQUFPO0FBQUEsTUFDTCxLQUFLLEtBQUssUUFBUSxrQ0FBVyxVQUFVO0FBQUEsTUFDdkMsV0FBVyxLQUFLLFFBQVEsa0NBQVcsVUFBVTtBQUFBLE1BQzdDLFlBQVksS0FBSyxRQUFRLGtDQUFXLFdBQVc7QUFBQSxJQUNqRDtBQUFBLEVBQ0Y7QUFDRixFQUFFO0FBRUYsUUFBUSxJQUFJLDZDQUE2QyxLQUFLLElBQUksQ0FBQztBQUNuRSxTQUFTLGdCQUF3QjtBQUMvQixTQUFPO0FBQUEsSUFDTCxNQUFNO0FBQUEsSUFDTixPQUFPO0FBQUE7QUFBQSxJQUNQLGdCQUFnQixRQUFRO0FBRXRCLGFBQU8sWUFBWSxJQUFJLENBQUMsS0FBSyxLQUFLLFNBQVM7QUFDekMsY0FBTSxNQUFNLElBQUksT0FBTztBQUN2QixZQUFJLElBQUksV0FBVyxRQUFRLEtBQUssQ0FBQyxJQUFJLFdBQVcsTUFBTSxLQUFLLENBQUMsSUFBSSxTQUFTLEdBQUcsR0FBRztBQUM3RSxjQUFJLE1BQU07QUFBQSxRQUNaO0FBQ0EsYUFBSztBQUFBLE1BQ1AsQ0FBQztBQUFBLElBTUg7QUFBQSxFQUNGO0FBQ0Y7IiwKICAibmFtZXMiOiBbXQp9Cg==
