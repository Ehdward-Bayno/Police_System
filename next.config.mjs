let userConfig = undefined
try {
<<<<<<< HEAD
  // try to import ESM first
  userConfig = await import('./v0-user-next.config.mjs')
} catch (e) {
  try {
    // fallback to CJS import
    userConfig = await import("./v0-user-next.config");
  } catch (innerError) {
    // ignore error
  }
=======
  userConfig = await import('./v0-user-next.config')
} catch (e) {
  // ignore error
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
}

/** @type {import('next').NextConfig} */
const nextConfig = {
  eslint: {
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  images: {
    unoptimized: true,
  },
  experimental: {
    webpackBuildWorker: true,
    parallelServerBuildTraces: true,
    parallelServerCompiles: true,
  },
}

<<<<<<< HEAD
if (userConfig) {
  // ESM imports will have a "default" property
  const config = userConfig.default || userConfig

  for (const key in config) {
=======
mergeConfig(nextConfig, userConfig)

function mergeConfig(nextConfig, userConfig) {
  if (!userConfig) {
    return
  }

  for (const key in userConfig) {
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
    if (
      typeof nextConfig[key] === 'object' &&
      !Array.isArray(nextConfig[key])
    ) {
      nextConfig[key] = {
        ...nextConfig[key],
<<<<<<< HEAD
        ...config[key],
      }
    } else {
      nextConfig[key] = config[key]
=======
        ...userConfig[key],
      }
    } else {
      nextConfig[key] = userConfig[key]
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
    }
  }
}

export default nextConfig
