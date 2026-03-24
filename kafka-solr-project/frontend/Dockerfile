FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package.json and install dependencies
COPY package.json ./
# If package-lock.json exists, it'll be copied too
COPY package-lock.json* ./

RUN npm install

# Copy all files
COPY . .

# Expose the development port
EXPOSE 3000

# Start the Vite/CRA development server
CMD ["npm", "start"]
